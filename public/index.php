<?php

/**
 * Mytory Docs — Entry point.
 * 
 * Routes:
 *   GET  /                    → home (list all doc_roots)
 *   GET  /list/{root}[/path]  → directory listing
 *   GET  /view/{root}/.../file → view document
 *   GET  /edit/{root}/.../file → edit document
 *   POST /save/{root}/.../file → save document (JSON API)
 *   POST /backup/{root}/.../file → backup document (JSON API)
 *   GET  /image/{root}/.../file → image proxy
 *   GET  /search               → search page
 *   GET  /api/search?q=...     → search JSON API
 *   POST /new-file/{root}[/path] → create new file
 *   POST /delete-file/{root}/.../file → delete file
 */

declare(strict_types=1);

// Auto-create config.php from sample if missing
if (!is_file(__DIR__ . '/../config.php')) {
    if (is_file(__DIR__ . '/../config.sample.php')) {
        copy(__DIR__ . '/../config.sample.php', __DIR__ . '/../config.php');
    } else {
        http_response_code(500);
        echo 'config.php not found. Copy config.sample.php to config.php and edit it.';
        exit;
    }
}

require __DIR__ . '/../config.php';
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/PathParser.php';
require __DIR__ . '/../src/FrontMatter.php';
require __DIR__ . '/../src/FileUtils.php';
require __DIR__ . '/../src/Fts5Index.php';
require __DIR__ . '/../src/MarkdownRenderer.php';

// ── Parse request URI ────────────────────────────────────
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$method = $_SERVER['REQUEST_METHOD'];
$viewDir = __DIR__ . '/../src/views';

// Remove trailing slash
$uri = rtrim($uri, '/') ?: '/';

// Serve static files when using PHP built-in server
if (PHP_SAPI === 'cli-server') {
    $staticFile = __DIR__ . $uri;
    if (is_file($staticFile)) {
        return false;  // Let PHP built-in server handle it
    }
}

// ── Routing ──────────────────────────────────────────────

// Helper: extract {root} and {path} from URI segments
function parseRoute(string $uri, int $skipSegments = 1): array
{
    $segments = explode('/', ltrim($uri, '/'));
    $rootName = $segments[$skipSegments] ?? '';
    $rest = implode('/', array_slice($segments, $skipSegments + 1));
    return [$rootName, $rest];
}

// Helper: build path string in the old format for PathParser
function buildPathString(string $cmd, string $rootName, string $rest): string
{
    return $cmd === 'new-file' || $cmd === 'delete-file'
        ? "{$cmd}:{$rootName}/{$rest}"
        : "{$cmd}:{$rootName}/{$rest}";
}

// ── POST /save/{root}/... ────────────────────────────────
if ($method === 'POST' && preg_match('#^/save/([^/]+)/(.+)$#', $uri, $m)) {
    header('Content-Type: application/json');
    try {
        $pathString = "edit:{$m[1]}/{$m[2]}";
        $parsed = PathParser::parse($pathString);
        $realFile = $parsed['real_full_path'] . DIRECTORY_SEPARATOR . basename($m[2]);
        
        // Resolve actual file path
        if (!is_file($realFile)) {
            $realFile = $parsed['real_full_file'] ?: $realFile;
        }
        if (!is_file($realFile)) {
            http_response_code(404);
            echo json_encode(['code' => 'fail', 'msg' => 'File not found']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input') ?: '{}', true);
        $content = $input['content'] ?? '';
        $currentMtime = (int)($input['current_filemtime'] ?? 0);
        $realMtime = filemtime($realFile);

        if ($realMtime > $currentMtime) {
            echo json_encode([
                'code' => 'file_changed',
                'msg' => 'File changed externally.',
                'real_filemtime' => $realMtime,
            ]);
            exit;
        }

        $newMtime = FileUtils::writeContent($realFile, $content);

        // Update search index
        try {
            $fts = new Fts5Index();
            $fts->upsert($realFile);
        } catch (\Throwable) {}

        echo json_encode(['code' => 'success', 'real_filemtime' => $newMtime]);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['code' => 'fail', 'msg' => $e->getMessage()]);
    }
    exit;
}

// ── POST /backup/{root}/... ──────────────────────────────
if ($method === 'POST' && preg_match('#^/backup/([^/]+)/(.+)$#', $uri, $m)) {
    header('Content-Type: application/json');
    try {
        $pathString = "edit:{$m[1]}/{$m[2]}";
        $parsed = PathParser::parse($pathString);
        $realFile = $parsed['real_full_file'] ?: ($parsed['real_full_path'] . DIRECTORY_SEPARATOR . basename($m[2]));
        
        $input = json_decode(file_get_contents('php://input') ?: '{}', true);
        $content = $input['content'] ?? '';

        $backupDir = BACKUP_PATH . DIRECTORY_SEPARATOR . $parsed['full_path'];
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $timestamp = date('Y-m-d_H_i_s');
        $backupFile = $backupDir . DIRECTORY_SEPARATOR . $timestamp . '__' . basename($realFile);
        file_put_contents($backupFile, $content);

        echo json_encode(['code' => 'success']);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['code' => 'fail', 'msg' => $e->getMessage()]);
    }
    exit;
}

// ── POST /new-file/{root}[/path] ─────────────────────────
if ($method === 'POST' && preg_match('#^/new-file/([^/]+)(/.*)?$#', $uri, $m)) {
    $rootName = $m[1];
    $subPath = ltrim($m[2] ?? '', '/');
    $filename = $_POST['filename'] ?? '';

    if ($filename === '') {
        http_response_code(400);
        echo 'Filename required';
        exit;
    }

    global $doc_roots;
    if (!isset($doc_roots[$rootName])) {
        http_response_code(404);
        echo 'Unknown doc_root';
        exit;
    }

    $dirPath = $doc_roots[$rootName] . ($subPath ? '/' . $subPath : '');
    try {
        FileUtils::createFile($dirPath, $filename);
        $editUrl = '/edit/' . rawurlencode($rootName) . ($subPath ? '/' . rawurlencode($subPath) : '') . '/' . rawurlencode($filename);
        header('Location: ' . $editUrl);
    } catch (\RuntimeException $e) {
        http_response_code(409);
        echo $e->getMessage();
    }
    exit;
}

// ── POST /delete-file/{root}/... ─────────────────────────
if ($method === 'POST' && preg_match('#^/delete-file/([^/]+)/(.+)$#', $uri, $m)) {
    $pathString = "view:{$m[1]}/{$m[2]}";
    $parsed = PathParser::parse($pathString);
    $realFile = $parsed['real_full_file'] ?: ($parsed['real_full_path'] . DIRECTORY_SEPARATOR . basename($m[2]));

    try {
        FileUtils::deleteFile($realFile);
        // Remove from index
        try { (new Fts5Index())->delete($realFile); } catch (\Throwable) {}
        header('Location: /list/' . rawurlencode($parsed['full_path']));
    } catch (\RuntimeException $e) {
        http_response_code(404);
        echo $e->getMessage();
    }
    exit;
}

// ── GET /image/{root}/... ────────────────────────────────
if ($method === 'GET' && preg_match('#^/image/([^/]+)/(.+)$#', $uri, $m)) {
    $pathString = "image:{$m[1]}/{$m[2]}";
    $parsed = PathParser::parse($pathString);
    $realFile = $parsed['real_full_file'] ?: ($parsed['real_full_path'] . DIRECTORY_SEPARATOR . basename($m[2]));

    if (!is_file($realFile)) {
        http_response_code(404);
        exit;
    }

    $mime = mime_content_type($realFile);
    header("Content-Type: {$mime}");
    readfile($realFile);
    exit;
}

// ── GET /api/search ──────────────────────────────────────
if ($method === 'GET' && $uri === '/api/search') {
    header('Content-Type: application/json');
    $q = $_GET['q'] ?? '';
    $root = $_GET['root'] ?? null;
    $rebuild = ($_GET['_rebuild'] ?? '') === '1';

    if ($q === '' && !$rebuild) {
        echo json_encode(['results' => [], 'query' => '']);
        exit;
    }

    try {
        $fts = new Fts5Index();
        if ($rebuild) {
            $count = $fts->rebuild();
            echo json_encode(['rebuilt' => true, 'files' => $count]);
            exit;
        }
        $results = $fts->search($q, $root);
        echo json_encode(['results' => $results, 'query' => $q]);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ── GET /search ──────────────────────────────────────────
if ($method === 'GET' && $uri === '/search') {
    $q = $_GET['q'] ?? '';
    $root = $_GET['root'] ?? null;
    $results = [];
    $error = null;

    if ($q !== '') {
        try {
            $fts = new Fts5Index();
            $results = $fts->search($q, $root);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }
    }

    global $doc_roots;
    $rootNames = array_keys($doc_roots);
    $pageTitle = ($q ? "'{$q}' — " : '') . 'Search : Mytory Docs';
    require $viewDir . '/layout.php';
    exit;
}

// ── GET /view/{root}/... ─────────────────────────────────
if ($method === 'GET' && preg_match('#^/view/([^/]+)/(.+)$#', $uri, $m)) {
    $pathString = "view:{$m[1]}/{$m[2]}";
    $parsed = PathParser::parse($pathString);
    $realFile = $parsed['real_full_file'] ?: ($parsed['real_full_path'] . DIRECTORY_SEPARATOR . basename($m[2]));

    if (!is_file($realFile)) {
        http_response_code(404);
        echo '<h1>404 — File not found</h1>';
        exit;
    }

    $content = FileUtils::readContent($realFile);
    $frontMatter = FrontMatter::parse($content);
    $isPlain = strtolower(pathinfo($realFile, PATHINFO_EXTENSION)) === 'txt';

    if ($isPlain) {
        $html = '<pre class="text-sm font-mono" style="white-space:pre-wrap">' . htmlspecialchars($content) . '</pre>';
    } else {
        $renderer = new MarkdownRenderer();
        $imgProxyBase = $parsed['root_name'] . '/' . $parsed['relative_path'];
        $html = $renderer->render($content, $imgProxyBase);
    }

    // Title for <title> tag
    $pageTitle = ($frontMatter['title'] ?? FileUtils::extractTitle($realFile)) . ' : Mytory Docs';

    require $viewDir . '/layout.php';
    exit;
}

// ── GET /edit/{root}/... ─────────────────────────────────
if ($method === 'GET' && preg_match('#^/edit/([^/]+)/(.+)$#', $uri, $m)) {
    $pathString = "edit:{$m[1]}/{$m[2]}";
    $parsed = PathParser::parse($pathString);
    $realFile = $parsed['real_full_file'] ?: ($parsed['real_full_path'] . DIRECTORY_SEPARATOR . basename($m[2]));

    if (!is_file($realFile)) {
        http_response_code(404);
        echo '<h1>404 — File not found</h1>';
        exit;
    }

    $content = FileUtils::readContent($realFile);
    $pageTitle = 'Editing: ' . (FrontMatter::parse($content)['title'] ?? FileUtils::extractTitle($realFile));

    require $viewDir . '/layout.php';
    exit;
}

// ── GET /list/{root}[/path] ──────────────────────────────
if ($method === 'GET' && preg_match('#^/list/([^/]+)(/.*)?$#', $uri, $m)) {
    $rootName = $m[1];
    $subPath = ltrim($m[2] ?? '', '/');

    global $doc_roots;
    if (!isset($doc_roots[$rootName])) {
        http_response_code(404);
        echo '<h1>404 — Unknown doc_root</h1>';
        exit;
    }

    $dirPath = $doc_roots[$rootName] . ($subPath ? '/' . $subPath : '');
    if (!is_dir($dirPath)) {
        http_response_code(404);
        echo '<h1>404 — Directory not found</h1>';
        exit;
    }

    // Build $parsed for list.php view (link construction)
    $parsed = [
        'root_name'     => $rootName,
        'relative_path' => $subPath,
        'full_path'     => $rootName . ($subPath ? '/' . $subPath : ''),
    ];

    $listing = FileUtils::listDirectory($dirPath);
    $pageTitle = $rootName . ($subPath ? ' / ' . $subPath : '') . ' : Mytory Docs';

    // Build breadcrumbs (each segment clickable)
    $breadcrumbs = [];
    $breadcrumbs[] = ['label' => $rootName, 'url' => "/list/{$rootName}"];
    if ($subPath !== '') {
        $parts = explode('/', $subPath);
        $accum = $rootName;
        foreach ($parts as $part) {
            $accum .= '/' . $part;
            $breadcrumbs[] = ['label' => $part, 'url' => '/list/' . rawurlencode($accum)];
        }
    }

    // Parent folder link
    $parentPath = null;
    if ($subPath !== '') {
        $parent = dirname($subPath);
        $parentPath = $parent === '.' ? "/list/{$rootName}" : "/list/{$rootName}/{$parent}";
    }

    require $viewDir . '/layout.php';
    exit;
}

// ── GET / (Home) ─────────────────────────────────────────
if ($uri === '/') {
    global $doc_roots;
    $pageTitle = 'Mytory Docs';
    require $viewDir . '/layout.php';
    exit;
}

// ── 404 ──────────────────────────────────────────────────
http_response_code(404);
echo '<h1>404</h1><p>Page not found.</p>';
