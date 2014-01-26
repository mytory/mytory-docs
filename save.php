<?php
include 'config.php';
include 'functions.php';

$parsed = parse_path();

$current_file_content = file_get_contents($parsed['real_full_file']);
$current_md5 = md5($current_file_content);
$content_saved_md5 = $_REQUEST['content_saved_md5'];

if($current_md5 != $content_saved_md5){
	echo json_encode(array(
		'current_md5' => $current_md5,
		'content_saved_md5' => $content_saved_md5,
		'code' => 'file changed',
		'msg' => '파일이 밖에서 변경됐습니다.',
		'current_file_content' => $current_file_content,
	));
	exit;
}

$handle = fopen($parsed['real_full_file'], 'w');
if( ! $handle){
	echo json_encode(array(
		'code' => 'fail',
		'msg' => '열다가 실패',
		'real_full_file' => $parsed['real_full_file']
	));
	exit;
}

if( ! fwrite($handle, $_POST['content'])){
	echo json_encode(array(
		'code' => 'fail',
		'msg' => '쓰다가 실패',
		'content' => $_POST['content']
	));
	exit;	
}

fclose($handle);

echo json_encode(array(
	'code' => 'success',
	'content_saved_md5' => md5($_POST['content']),
));
