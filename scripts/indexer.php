#!/usr/bin/env php
<?php

/**
 * Mytory Docs — FTS5 Indexer CLI
 *
 * Usage:
 *   php scripts/indexer.php rebuild         Full re-index of all doc_roots
 *   php scripts/indexer.php update <file>   Update single file in index
 *   php scripts/indexer.php delete <file>   Remove file from index
 *   php scripts/indexer.php watch           Watch mode: read file paths from stdin
 *
 * Watch mode is designed to be piped from fswatch / inotifywait:
 *   fswatch -0 doc_roots/ | php scripts/indexer.php watch
 */

declare(strict_types=1);

// CLI bootstrap
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script is CLI-only.\n");
    exit(1);
}

// Minimal bootstrap (avoid web-specific constants)
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['QUERY_STRING'] = '';

define('ROOT', dirname(__DIR__));
define('BACKUP_PATH', ROOT . '/backup');

if (!defined('OS_ENCODING')) {
    define('OS_ENCODING', PHP_OS_FAMILY === 'Windows' ? 'CP949' : 'utf-8');
}

require ROOT . '/config.php';
require ROOT . '/vendor/autoload.php';
require ROOT . '/src/PathParser.php';
require ROOT . '/src/FrontMatter.php';
require ROOT . '/src/FileUtils.php';
require ROOT . '/src/Fts5Index.php';

$command = $argv[1] ?? 'help';

switch ($command) {
    case 'rebuild':
        $start = microtime(true);
        $count = (new Fts5Index())->rebuild();
        $elapsed = round(microtime(true) - $start, 2);
        fwrite(STDERR, "[indexer] Rebuilt: {$count} files in {$elapsed}s\n");
        echo "{$count} files indexed in {$elapsed}s\n";
        break;

    case 'update':
        if (empty($argv[2])) {
            fwrite(STDERR, "Usage: php scripts/indexer.php update <file>\n");
            exit(1);
        }
        (new Fts5Index())->upsert($argv[2]);
        fwrite(STDERR, "[indexer] Updated: {$argv[2]}\n");
        break;

    case 'delete':
        if (empty($argv[2])) {
            fwrite(STDERR, "Usage: php scripts/indexer.php delete <file>\n");
            exit(1);
        }
        (new Fts5Index())->delete($argv[2]);
        fwrite(STDERR, "[indexer] Deleted: {$argv[2]}\n");
        break;

    case 'watch':
        fwrite(STDERR, "[indexer] Watch mode started. Waiting for file paths from stdin...\n");
        $fts = new Fts5Index();
        global $markdown_ext_list;

        while ($file = fgets(STDIN)) {
            $file = trim($file);
            if ($file === '') continue;

            // Skip non-content directories
            if (preg_match('#/(\.git|vendor|node_modules|backup|\.Trash|\.DS_Store)/#', $file)) continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $markdown_ext_list, true) && $ext !== '') continue;

            if (is_file($file)) {
                try {
                    $fts->upsert($file);
                    fwrite(STDERR, "[indexer] Updated: {$file}\n");
                } catch (\Throwable $e) {
                    fwrite(STDERR, "[indexer] Error updating {$file}: {$e->getMessage()}\n");
                }
            } elseif (!file_exists($file)) {
                try {
                    $fts->delete($file);
                    fwrite(STDERR, "[indexer] Deleted: {$file}\n");
                } catch (\Throwable $e) {
                    fwrite(STDERR, "[indexer] Error deleting {$file}: {$e->getMessage()}\n");
                }
            }
        }
        break;

    default:
        echo <<<HELP
Mytory Docs — FTS5 Indexer

Usage:
  php scripts/indexer.php rebuild          Full re-index of all doc_roots
  php scripts/indexer.php update <file>     Update single file
  php scripts/indexer.php delete <file>     Remove file from index
  php scripts/indexer.php watch            Watch mode (pipe from fswatch/inotifywait)

Examples:
  php scripts/indexer.php rebuild
  fswatch -0 /Users/mytory/Documents/글 | php scripts/indexer.php watch

HELP;
        break;
}
