<?php

/**
 * User configuration for Mytory Docs.
 * Copy this file to config.php and customize.
 */

declare(strict_types=1);

if (!defined('OS_ENCODING')) {
    define('OS_ENCODING', 'utf-8');
}

// Document roots: label => absolute path
$doc_roots = [
    'docs' => __DIR__ . '/docs',
];

// Markdown file extensions to render (not just download)
$markdown_ext_list = [
    'md',
    'txt',
];

// Application URL (used for auto-save SSE, etc.)
// Auto-detected from request; override only if behind proxy.
$app_url = null;  // e.g. 'https://docs.example.com'

$timezone = 'Asia/Seoul';
