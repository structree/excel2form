<?php
ini_set("memory_limit", "500M");
ini_set("post_max_size", "20M");
ini_set("upload_max_filesize", "20M");
error_reporting(E_ALL & ~E_NOTICE);
error_reporting(1);
define("SITENAME",     "excel2form");
// autoloadを読み込む
require_once __DIR__.'/vendor/autoload.php';

date_default_timezone_set('Asia/tokyo');

//Utility Class
require_once __DIR__.'/classes/utility.php';
//Struct Class
require_once __DIR__.'/classes/struct.php';
//Exception Class
require_once __DIR__.'/classes/exception.php';
//Zip Class
require_once __DIR__.'/classes/zip.php';
//項目key
$colmnKey = [
		'main_category',
		'sub_category',
		'group',
		'data_name',
		'html_type',
		'element_name',
		'label_name',
		'value',
		'other_class',
		'place_holder',
		'append_text',
		'max_length',
		'data-keypad_max',
		'data_ret',
		'data_freeplace',
		'front_function'
];