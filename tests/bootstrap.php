<?php

/**
 * Test bootstrap — sets up minimal environment without web context.
 */

declare(strict_types=1);

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['QUERY_STRING'] = '';

// Test constants (only if not defined by phpunit.xml)
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}
if (!defined('BACKUP_PATH')) {
    define('BACKUP_PATH', ROOT . '/tests/_temp/backup');
}

if (!defined('OS_ENCODING')) {
    define('OS_ENCODING', 'utf-8');
}

// Test doc_roots — a temp directory
$testRoot = ROOT . '/tests/_temp/docs';
if (!is_dir($testRoot)) {
    mkdir($testRoot, 0777, true);
}
if (!is_dir(BACKUP_PATH)) {
    mkdir(BACKUP_PATH, 0777, true);
}

// Global variables that src/ classes expect
$GLOBALS['doc_roots'] = [
    'test'    => $testRoot,
    'missing' => '/nonexistent/path/12345',
];

$GLOBALS['markdown_ext_list'] = ['md', 'txt'];

$GLOBALS['timezone'] = 'Asia/Seoul';
date_default_timezone_set('Asia/Seoul');

// Composer autoload
require ROOT . '/vendor/autoload.php';

// Source files
require ROOT . '/src/PathParser.php';
require ROOT . '/src/FrontMatter.php';
require ROOT . '/src/FileUtils.php';
require ROOT . '/src/Fts5Index.php';
require ROOT . '/src/MarkdownRenderer.php';
