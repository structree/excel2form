<?php

//ユーティリティClass

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

class Utility {

	public $config;

	public function __construct($data) {
		$this->config = $data;
	}

	public function readSheetIndex($filename) {
		//取り込むxlsx
		$reader = new XlsxReader();
		$spreadsheet = $reader->load($filename); // ファイルをロードするとSpreadsheetが得られます。
		$sheetNames = $reader->listWorksheetInfo($filename);

		return $sheetNames;
	}

	//エクセルファイルを読み込む
	public function readXlsx($sheetNumber, $sheetInfo) {
		//取り込むxlsx
		$filename = $this->config["xlsxPath"];
		$reader = new XlsxReader();
		$spreadsheet = $reader->load($filename); // ファイルをロードするとSpreadsheetが得られます。
		$sheet = $spreadsheet->getSheet($sheetNumber); // 最初のシートを得ています。Sheetが得られます。
		$dataArray = $sheet->rangeToArray("{$this->config["start_row"]}:" . $this->config["end_col"] . $sheetInfo["totalRows"]); // これで表の2次元配列が得られます。

		return $this->arrayNullChecker($dataArray);
	}

	//2階層の連想配列内が全て空だった場合、その行を削除する。後で再帰的な処理に変更
	public function arrayNullChecker($dataArray) {
		$returnArray = [];
		foreach ($dataArray as $data) {
			$tmpCounter = 0;
			foreach ($data as $key => $value) {
				if (!empty($value)) {
					$tmpCounter++;
				}
			}
			if ($tmpCounter > 0) {
				array_push($returnArray, $data);
			}
		}
		return $returnArray;
	}

	public function compact($data, $colmnKey) {
		//変数とキーを連想配列化
		$line = [];
		$records = [];
		for ($i = 0; $i < count($data); $i++) {
			for ($k = 0; $k < count($data[$i]); $k++) {
				$line[$colmnKey[$k]] = $data[$i][$k];
			}
			$records[] = $line;
		}

		return $records;
	}

	//ユニークな配列を返す
	public static function getUniqueCategory($resultCsv, $keyName) {
		$CategoryArray = [];
		for ($j = 0; $j < count($resultCsv); $j++) {
			if (is_null($resultCsv[$j][$keyName])) {
				$CategoryArray[] = "";
			} else {
				$CategoryArray[] = $resultCsv[$j][$keyName];
			}
		}
		return array_merge(array_unique($CategoryArray));
	}

	public function makeHTML($name, $struct, $type) {
		//生成するhtml名
		$htmlName = $this->config["html_path"] . $type . "_" . $this->config["basename"] . "_" . $name . ".html";
		// 出力のバッファリングを有効にする
		ob_start();
		// Render our view
		$this->rendering($name, $struct, $type);
		// 同階層の test.html にphp実行結果を出力
		file_put_contents($htmlName, ob_get_contents());
		// 出力用バッファをクリア(消去)し、出力のバッファリングをオフにする
		ob_end_clean();

		return $htmlName;
	}

	public function rendering($name, $struct, $type) {
		// Specify our Twig templates location
		$loader = new Twig_Loader_Filesystem($this->config["rootDir"] . '/templates');
		// Instantiate our Twig
		$twig = new Twig_Environment($loader, ['debug' => true]);
		$twig->addExtension(new Twig_Extension_Debug());

		$layout = $twig->load('layout.html');

		echo $layout->renderBlock($type, ["struct" => $struct, "companyName" => $this->config["basename"], "formName" => $name]);
	}

	public function makedir($dir) {
		$mydir = $this->config["work_dir"] . "/" . $dir . "/";
		if (!is_dir($mydir)) {
			//ディレクトリ構造
			mkdir($mydir, 0755);
			chmod($mydir, 0755);
		}
		return $mydir;
	}

	public static function writeLog($filePath, $fileName, $data) {
		// 出力のバッファリングを有効にする
		ob_start();
		print_r($data);
		// 同階層の test.html にphp実行結果を出力
		file_put_contents($filePath . $fileName . ".txt", ob_get_contents());
		// 出力用バッファをクリア(消去)し、出力のバッファリングをオフにする
		ob_end_clean();
	}

	public function regex2Replace($type, $regexList, $pageData) {
		foreach ($regexList[$type] as $thisRegex) {
			$pageData = preg_replace('/' . $thisRegex["regex"], $thisRegex["replace"], $pageData);
		}
		return $pageData;
	}

	public static function execInBackground($cmd) {
		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B " . $cmd, "r"));
		} else {
			exec($cmd . " > /dev/null &");
		}
		return $cmd;
	}

	//$dir 取得したいフォルダパス
	//$zipFileSavePath 一時、zipを保存しておくフォルダパス
	public static function getZip($dir, $zipFileSavePath) {

		// zipファイル名
		$fileName = "excel2html";
		// 圧縮対象フォルダ
		$compressDir = $dir;

		// コマンド
		// cd：ディレクトリの移動
		// zip:zipファイルの作成
		$command = "cd " . $compressDir . ";" .
						"zip -r " . $zipFileSavePath . "/" . $fileName . ".zip .";

		$datetime = "hoge";
		// Linuxコマンドの実行
		Utility::execInBackground($command);
		Utility::writeLog("log/{$datetime}", "_excec_log.", $command);

		return $fileName . ".zip";
		//    消す
		//unlink($zipFileSavePath.$fileName.".zip");
	}

	public static function dir_copy($dir_name, $new_dir) {
		if (!is_dir($new_dir)) {
			mkdir($new_dir);
		}

		if (is_dir($dir_name)) {
			if ($dh = opendir($dir_name)) {
				while (($file = readdir($dh)) !== false) {
					if ($file == "." || $file == "..") {
						continue;
					}
					if (is_dir($dir_name . "/" . $file)) {
						self::dir_copy($dir_name . "/" . $file, $new_dir . "/" . $file);
					} else {
						copy($dir_name . "/" . $file, $new_dir . "/" . $file);
					}
				}
				closedir($dh);
			}
		}
		return true;
	}

}
