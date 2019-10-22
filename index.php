<?php
/*
 * Excel2form
 * author : structree
 * make : 2019
*/

require_once __DIR__.'/bootstrap.php';

//値渡し
$r_kind='top';
isset($_GET['r_kind']) && $r_kind=$_GET['r_kind'];
isset($_POST['r_kind']) && $r_kind=$_POST['r_kind'];
isset($_POST['token']) && $token=$_POST['token'];

//フロントテンプレートエンジン twig
$loader = new Twig_Loader_Filesystem(__DIR__.'/page');
 // Instantiate our Twig
$twig = new Twig_Environment($loader,['debug' => false]);
$twig->addExtension(new Twig_Extension_Debug());

// セッションを開始する
session_start();

//エラー処理
if(empty($_POST) && $_SERVER['REQUEST_METHOD']=='POST'){
    //中身の無いPOST対応
    $errorMsg = '適切なリクエストではありません。';
    $r_kind='top';
}
//アップロードエラー
if(isset($_FILES['excelFile']['type'])){
    $type = $_FILES['excelFile']['type'];
    $excelType = array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $errorMsg='';
    //エラー判別
    if($_FILES["excelFile"]["error"] != UPLOAD_ERR_OK || !in_array($type,$excelType)){
        $errorMsg='';
        $errorMsg = UploadException::codeToMessage($_FILES['excelFile']['error']);

        if(empty($_POST)){
            $errorMsg = '適切なフォーマットではないか容量が大きすぎます。';
        }elseif (!in_array($type,$excelType)) {
            $errorMsg = 'アップロードファイルの種類が正しくありません。';
        }
        $r_kind='top';
    }
}

if($r_kind=='top'){
    //初期画面

    // トークンを発行する
    $token = md5(uniqid(rand(), true));
    // トークンをセッションに保存
    $_SESSION['excel2html_onetime_token'] = $token;

	echo $twig->render('index.html',[
	    "site"=>SITENAME
        ,'url'=>$_SERVER['SCRIPT_NAME']
        ,'error'=>$errorMsg
        ,'token'=>$token
    ]);

}elseif($r_kind=='run'){

    // 多重送信対策
    $session_token = isset($_SESSION['excel2html_onetime_token']) ? $_SESSION['excel2html_onetime_token'] : '';

    if ($token === '' || $token !== $session_token ) {
        die("多重送信はご遠慮ください。");
    }
    unset($_SESSION['excel2html_onetime_token']);

	if($_FILES["excelFile"]["error"] == UPLOAD_ERR_OK && in_array($type,$excelType)){
		#アップロードファイルの格納
		$location_name = $_FILES["excelFile"]["tmp_name"];
		$tmp_name = "tmpwork.xlsx";
		//固有ディレクトリ作成(timestamp+randでハッシュ化)
		$date = new DateTime();
		$datetime = $date->format("Y-m-d_His");
		$rand = mt_rand();
		$hash= md5($datetime.$rand);
		$work_dir="./work";
		mkdir("{$work_dir}/{$hash}/");
		chmod("{$work_dir}/{$hash}/",0755);
		mkdir("{$work_dir}/{$hash}/xlsx");
		
		$xlsxPath = "{$work_dir}/{$hash}/xlsx/{$tmp_name}";
		//テンポラリーからwork/へファイル移動
		move_uploaded_file($location_name, $xlsxPath);
		$basename = explode('.xlsx',$_FILES["excelFile"]["name"]);
		$config = [
			"start_row" => "A4",
			"end_col"=>"W",
			"work_dir"=>"{$work_dir}/{$hash}",
			"html_path" => "{$work_dir}/{$hash}/html/",
			"rootDir"=>__DIR__,
			"type"=>[
					"input",//入力画面
//					"confirm"//確認画面
			],
			"xlsxPath"=>$xlsxPath,
			"hash"=>$hash,
			"date_time"=>$datetime,
			"basename"=>$basename[0],
			"debug_option"=>1
		];
	//データ読み込み
	$toolbox = new Utility($config);
	$sheetIndex = $toolbox->readSheetIndex($xlsxPath);
	$htmlPath = $toolbox->makedir('html');
	$toolbox->makedir('html');
	$toolbox->makedir('html/css');
	$toolbox->makedir('html/js');
	if($config["debug_option"] == 1){
	$logPath = $toolbox->makedir('log');
	$toolbox->writeLog($logPath, "configLog", $config);
	}

	/*=========================================================*/
	/* HTML作成 */
	/*=========================================================*/

	//出力したhtml名を記憶
	$outputHtmls = [];

	//Excel->HTML
	foreach ($sheetIndex as $sheetNumber => $sheetInfo) {
		try {
		//Excel読み込み
		$xlsx = $toolbox->readXlsx($sheetNumber,$sheetInfo);
		//読み込んだ配列にカラム名をセット
		$compact = $toolbox->compact($xlsx,$colmnKey);

		//構造体作成インスタンス
		$maker = new Struct($compact);

		foreach ($config["type"] as $type) {
			$tree = $maker->grouping($type)->makeTree();
			//HTMLを作成(返り値は作成したHTMLのフルパス)
			$tmpHtml = $toolbox->makeHTML($sheetInfo["worksheetName"],$tree->struct,$type);
			//出力したHTML名を格納
			$outputHtmls[$type][] = $tmpHtml;
			//コマンドライン引数で1が渡ってきていればログファイルに書き込む
			if($config["debug_option"] == 1){
			$toolbox->writeLog($logPath, "inputLog({$sheetInfo["worksheetName"]})", $tree->struct);
			}
		}

		} catch (Exception $exc) {
			$toolbox->writeLog($logPath, "errorLog", $exc->getTraceAsString());
		}
	}

	$zip = new MyZipArchive();
	//Zipファイル名指定
    $zipFileName = 'html.zip';
    $zipTmpDir = "./work/{$config["hash"]}/";
    $zip_file = $zipTmpDir . $zipFileName;

    //Zipファイルオープン
    $result = $zip->open($zip_file, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

    if ($result !== true) {
        return false;
    }

    //処理制限時間を外す
    set_time_limit(0);

    $zip->addDir($zipTmpDir.'/html/', 'html', true);

    $zip->close();

//	$zipName=Utility::getZip("/work/".$config["hash"]."/html/","/work/".$config["hash"]);
	echo $twig->render('complete.html',["zip_url"=>"./work/{$config["hash"]}/{$zipFileName}","site"=>SITENAME]);
	}
}
