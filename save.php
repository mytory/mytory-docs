<?php
include 'config.php';
include 'functions.php';

$parsed = parse_path();

$handle = @fopen($parsed['real_full_file'], 'w');
if( ! $handle){
	echo json_encode(array(
		'code' => 'fail',
		'msg' => '열다가 실패',
	));
	exit;
}

if( ! fwrite($handle, $_POST['content'])){
	echo json_encode(array(
		'code' => 'fail',
		'msg' => '쓰다가 실패',
	));
	exit;	
}

fclose($handle);

echo json_encode(array(
	'code' => 'success'
));
