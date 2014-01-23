<?php
include 'config.php';
include 'functions.php';

$parsed = parse_path();

var_dump($parsed);
exit;

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
    'code' => 'success'
));
