<?php

// auto
define('ROOT', dirname(__FILE__));
define('BASE_URL', $_SERVER['HTTP_HOST']);

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