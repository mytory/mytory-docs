<?php
include 'config.php';
include 'functions.php';
$parsed = parse_path();
$extension = pathinfo($parsed['real_full_file'], PATHINFO_EXTENSION);
header("content-type: image/" . $extension);
echo file_get_contents($parsed['real_full_file']);