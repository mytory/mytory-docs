<!DOCTYPE html>
<html lang="ko" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="/assets/build.css">
    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

<!-- Navigation -->
<nav class="border-b border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/80 backdrop-blur sticky top-0 z-10">
    <div class="max-w-3xl mx-auto px-4 py-2 flex items-center justify-between gap-4 text-sm">
        <div class="flex items-center gap-4">
            <a href="/" class="font-bold text-lg text-gray-900 dark:text-white no-underline hover:opacity-70">MD</a>
            
            <?php if (isset($parsed)): ?>
                <?php if (preg_match('#^/edit/#', $uri)): ?>
                    <a href="/list/<?= rawurlencode($parsed['full_path']) ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">List</a>
                    <a href="/view/<?= rawurlencode($parsed['root_name']) ?>/<?= rawurlencode($parsed['relative_path'] . '/' . $parsed['file']) ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">View</a>
                <?php elseif (preg_match('#^/view/#', $uri)): ?>
                    <a href="/list/<?= rawurlencode($parsed['full_path']) ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">List</a>
                    <a href="/edit/<?= rawurlencode($parsed['root_name']) ?>/<?= rawurlencode($parsed['relative_path'] . '/' . $parsed['file']) ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Edit</a>
                    <button id="toggle-heading-numbers" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 text-xs cursor-pointer">#</button>
                <?php elseif (preg_match('#^/list/#', $uri)): ?>
                    <button onclick="document.getElementById('new-file-dialog').showModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">New File</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="flex items-center gap-3">
            <!-- Search form -->
            <form action="/search" method="get" class="flex items-center gap-1">
                <input type="search" name="q" placeholder="Search…" 
                    class="w-32 lg:w-48 px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </form>
            
            <?php if (isset($parsed) && preg_match('#^/(edit|view)/#', $uri)): ?>
                <code class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-[200px] hidden sm:inline" title="<?= htmlspecialchars($parsed['real_full_file'] ?? '') ?>">
                    <?= htmlspecialchars(basename($parsed['real_full_file'] ?? '')) ?>
                </code>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Content -->
<main class="flex-1 max-w-3xl mx-auto px-4 py-8 w-full">

<?php if (preg_match('#^/view/#', $uri)): ?>
    <?php require __DIR__ . '/view.php'; ?>
<?php elseif (preg_match('#^/edit/#', $uri)): ?>
    <?php require __DIR__ . '/edit.php'; ?>
<?php elseif (preg_match('#^/list/#', $uri)): ?>
    <?php require __DIR__ . '/list.php'; ?>
<?php elseif ($uri === '/'): ?>
    <?php require __DIR__ . '/home.php'; ?>
<?php endif; ?>

</main>

<!-- Footer -->
<footer class="border-t border-gray-200 dark:border-gray-700 py-6 text-center text-xs text-gray-400 dark:text-gray-600">
    Mytory Docs
</footer>

<!-- New File Dialog -->
<dialog id="new-file-dialog" class="rounded-lg shadow-xl border dark:border-gray-600 bg-white dark:bg-gray-800 p-6 w-full max-w-sm backdrop:bg-black/50">
    <h2 class="text-lg font-semibold mb-4">New File</h2>
    <form method="post" action="/new-file/<?= rawurlencode(($parsed['root_name'] ?? '') . '/' . ($parsed['relative_path'] ?? '')) ?>">
        <input type="text" name="filename" placeholder="new-file.md" autofocus
            class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 mb-4">
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="px-3 py-1.5 rounded border border-gray-300 dark:border-gray-600 text-sm">Cancel</button>
            <button type="submit" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Create</button>
        </div>
    </form>
</dialog>

<!-- Delete Confirm Dialog -->
<dialog id="delete-dialog" class="rounded-lg shadow-xl border dark:border-gray-600 bg-white dark:bg-gray-800 p-6 w-full max-w-sm backdrop:bg-black/50">
    <h2 class="text-lg font-semibold mb-2">Delete?</h2>
    <p id="delete-dialog-message" class="text-sm text-gray-500 dark:text-gray-400 mb-4"></p>
    <form id="delete-dialog-form" method="post">
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="px-3 py-1.5 rounded border border-gray-300 dark:border-gray-600 text-sm">Cancel</button>
            <button type="submit" class="px-3 py-1.5 rounded bg-red-600 text-white text-sm font-medium hover:bg-red-700">Delete</button>
        </div>
    </form>
</dialog>

<script src="/assets/app.js"></script>
</body>
</html>
