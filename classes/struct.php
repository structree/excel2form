<?php
//構造体 Class

class Struct
{
	//読み込んだ生データ
	public $xlsx;
	//大分類カテゴリ
	public $category;
	//作業用
	public $work;
	//完成品
	public $struct;

	public function get(){
		return $this->xlsx;
	}
	
	public function __construct($data)
    {
		$this->xlsx = $data;
		$this->category = Utility::getUniqueCategory($data,"main_category");
    }
	

	public function grouping($type){
		$work = [];
		
		if($type==="input"){
			for ($i = 0; $i < count($this->xlsx); $i++) {
				$type = $this->xlsx[$i]["html_type"];

				$mainCategory = $this->xlsx[$i]["main_category"];
				$subCategory = $this->xlsx[$i]["sub_category"];
				$group = $this->xlsx[$i]["group"];
				$wrapCategory = "{$mainCategory}/{$subCategory}/{$group}";

				if($type =='text' || $type =='radio' || $type =='check'){
					$work[$wrapCategory][] = $this->xlsx[$i];
				}elseif($type =='select'){
				    if(!isset($work[$wrapCategory]["param"])){
                        $work[$wrapCategory]["param"] =
                            [
                                "html_type"=>$this->xlsx[$i]["html_type"],
                                "class"=>$this->xlsx[$i]["class"],
                                "other_class"=>$this->xlsx[$i]["other_class"],
                                "element_name"=>$this->xlsx[$i]["element_name"]
                            ];
				    }
					$work[$wrapCategory]["param"]["data"][] = $this->xlsx[$i];
				}
			}
		}elseif ($type==="confirm") {
			for ($i = 0; $i < count($this->xlsx); $i++) {
				$type = $this->xlsx[$i]["html_type"];
				$class = $this->xlsx[$i]["class"];
				$data_name=$this->xlsx[$i]["data_name"];
				$mainCategory = $this->xlsx[$i]["main_category"];
				$subCategory = $this->xlsx[$i]["sub_category"];
				$group = $this->xlsx[$i]["group"];
				$wrapCategory = "{$mainCategory}/{$subCategory}/{$group}";
				if($type =='text' && $class == 'handwritingItem' ){
					$work["{$mainCategory}/{$subCategory}/{$data_name}"] = $this->xlsx[$i];
				}
				elseif($type =='text' && $class !== 'handwritingItem'){
					$work[$wrapCategory][] = $this->xlsx[$i];
				}elseif($type =='radio' || $type =='check'){
					$work[$wrapCategory][] = $this->xlsx[$i];
				}elseif($type =='select'){
					$work[$wrapCategory]["param"] =
						[
							"html_type"=>$this->xlsx[$i]["html_type"],
							"class"=>$this->xlsx[$i]["class"],
							"other_class"=>$this->xlsx[$i]["other_class"],
							"element_name"=>$this->xlsx[$i]["element_name"]
						];
					$work[$wrapCategory]["param"]["data"][] = $this->xlsx[$i];
				}
			}
		};
		$this->work = $work;
		return $this;
	}
	public function makeTree(){
		$tmpRecords=[];
		foreach ($this->category as $key => $value){
			foreach ($this->work as $key2 => $value2){
				
				$split = explode("/",$key2);
				
				if($value == $split[0] ){
					$tmpRecords[$split[0]][$split[1]][$split[2]] = $value2;
				}
			}
		}
		$this->struct = $tmpRecords;
		return $this;
	}
}