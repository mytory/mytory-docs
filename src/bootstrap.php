<?php

/**
 * Application bootstrap.
 * Loaded once at the start of every request.
 */

declare(strict_types=1);

// ── Error reporting ──────────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// ── OS encoding ──────────────────────────────────────────
if (!defined('OS_ENCODING')) {
    if (PHP_OS_FAMILY === 'Windows') {
        define('OS_ENCODING', 'CP949');
    } else {
        define('OS_ENCODING', 'utf-8');
    }
}

// ── Paths ────────────────────────────────────────────────
define('ROOT', dirname(__DIR__));
define('BACKUP_PATH', ROOT . '/backup');

// ── Timezone ─────────────────────────────────────────────
$timezone = $timezone ?? 'Asia/Seoul';
date_default_timezone_set($timezone);

// ── Config validation ────────────────────────────────────
foreach ($doc_roots as $name => $root) {
    if (!is_dir($root)) {
        unset($doc_roots[$name]);
    }
}

// ── Autoload ─────────────────────────────────────────────
require ROOT . '/vendor/autoload.php';
