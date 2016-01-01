<?php
// auto. If you need, change.
require_once 'auto_config.php';

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
$markdown_ext_list = array(
	'md',
	'txt',
	// ... 
);

header("Content-Type: text/html; charset=UTF-8");