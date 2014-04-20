<?php
$real_filectime = filectime($_REQUEST['real_full_file']);
$editor_filectime = $_REQUEST['filectime'];

if($real_filectime != $editor_filectime){
	echo json_encode(array(
		'real_full_file' => $_REQUEST['real_full_file'],
		'real_filectime' => $real_filectime,
		'editor_filectime' => $editor_filectime,
		'code' => 'file changed',
		'msg' => '파일이 밖에서 변경됐습니다.'
	));
	exit;
}

$handle = fopen($_REQUEST['real_full_file'], 'w');
if( ! $handle){
	echo json_encode(array(
		'filectime' => filectime($_REQUEST['real_full_file']),
		'code' => 'fail',
		'msg' => '열다가 실패',
		'real_full_file' => $_REQUEST['real_full_file']
	));
	exit;
}

if( ! fwrite($handle, $_POST['content'])){
	fclose($handle);
	echo json_encode(array(
		'filectime' => filectime($_REQUEST['real_full_file']),
		'code' => 'fail',
		'msg' => '쓰다가 실패',
		'content' => $_POST['content']
	));
	exit;	
}

fclose($handle);

echo json_encode(array(
	'filectime' => filectime($_REQUEST['real_full_file']),
	'code' => 'success',
	'content_saved_md5' => md5($_POST['content']),
));
