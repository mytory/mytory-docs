<?php
include 'config.php';
include 'functions.php';

$parsed = parse_path();

date_default_timezone_set('Asia/Seoul');

$datetime = date('Y-m-d_H_i_s');
$backup_folder = BACKUP_PATH . DIRECTORY_SEPARATOR . $parsed['full_path'];
if (!is_dir($backup_folder)) {
    mkdir($backup_folder, 0777, true);
}
$handle = fopen($backup_folder .
    DIRECTORY_SEPARATOR . $datetime . '__' . $parsed['file'], 'w');
if (!$handle) {
    echo json_encode(array(
        'code' => 'fail',
        'msg' => '열다가 실패',
        'real_full_file' => $parsed['real_full_file']
    ));
    exit;
}

if (!fwrite($handle, $_POST['content'])) {
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
