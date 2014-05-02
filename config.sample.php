<?php

// auto. If you need, change.
if( ! isset($_SERVER['QUERY_STRING'])){
	$_SERVER['QUERY_STRING'] = '';
}

define('ROOT', dirname(__FILE__));
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace('/?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
define('BACKUP_PATH', ROOT . '/backup');

// set timezone. Select from http://www.php.net/manual/en/timezones.php
define('TIMEZONE', 'asia/seoul');
date_default_timezone_set(TIMEZONE);

// theme
$css_list = array(
	"bootstrap/css/bootstrap.min.css",
	"style.css",
	// "custom-theme/my-style.css",
);
$js_list = array(
	"bootstrap/js/bootstrap.min.js",
	//"custom-theme/script.js",
);
$template = array(
    'view' => 'view.php',
    'list' => 'list.php',
    'edit' => 'edit.php',
    'header' => 'header.php',
);

// docs
$doc_roots = array(
	'docs' => './docs',
	// 'other-folder' => '/home/mytory/other-folder'
);

// extension
$ext_list = array(
	'md',
	'txt',
	// ... 
);

header("Content-Type: text/html; charset=UTF-8");