<?php
include 'config.php';
include 'functions.php';
$parsed = parse_path();

$current_filemtime = $_REQUEST['current_filemtime'];
$real_filemtime = filemtime($parsed['real_full_file']);

if($real_filemtime > $current_filemtime){
	echo json_encode(array(
		'current_filemtime' => $current_filemtime,
		'real_filemtime' => $real_filemtime,
		'code' => 'file changed',
		'msg' => 'File changed externally.',
	));
	exit;
}

$handle = fopen($parsed['real_full_file'], 'w');
if( ! $handle){
	echo json_encode(array(
		'code' => 'fail',
		'msg' => 'Fail to open.',
		'real_full_file' => $parsed['real_full_file'],
	));
	exit;
}

if( ! fwrite($handle, $_POST['content'])){
	echo json_encode(array(
		'code' => 'fail',
		'msg' => 'Fail to write.',
		'content' => $_POST['content'],
	));
	exit;	
}

fclose($handle);

echo json_encode(array(
	'code' => 'success',
	'current_filemtime' => $current_filemtime,
	'real_filemtime' => $time + 1,
));
