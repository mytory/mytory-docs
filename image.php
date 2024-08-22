<?php
include 'config.php';
include 'functions.php';
$parsed = parse_path();
$extension = pathinfo($parsed['real_full_file'], PATHINFO_EXTENSION);
header("content-type: " . mime_content_type($parsed['real_full_file']));
echo file_get_contents($parsed['real_full_file']);