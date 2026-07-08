<!-- Search: full-text search results -->

<h1 class="text-xl font-bold mb-2">Search</h1>

<form action="/search" method="get" class="mb-6">
    <div class="flex gap-2">
        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search documents…" autofocus
            class="flex-1 px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
        
        <select name="root" class="px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-sm">
            <option value="">All doc_roots</option>
            <?php foreach ($rootNames as $rn): ?>
                <option value="<?= htmlspecialchars($rn) ?>" <?= ($root === $rn) ? 'selected' : '' ?>>
                    <?= htmlspecialchars(PathParser::convertFromOsEncoding($rn)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Search</button>
    </div>
</form>

<?php if ($error): ?>
    <div class="p-3 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded text-sm mb-4">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($q !== ''): ?>
    <?php if (empty($results)): ?>
        <p class="text-gray-500 dark:text-gray-400">No results for "<?= htmlspecialchars($q) ?>"</p>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
            <a href="/api/search?q=<?= urlencode($q) ?>" class="underline">View raw JSON</a>
        </p>
    <?php else: ?>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            <?= count($results) ?> result<?= count($results) > 1 ? 's' : '' ?> for "<?= htmlspecialchars($q) ?>"
        </p>
        <ul class="space-y-4">
            <?php foreach ($results as $r): 
                $displayRoot = PathParser::convertFromOsEncoding($r['root_name']);
                $relativePath = str_replace($doc_roots[$r['root_name']] ?? '', '', $r['path']);
                $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
                $viewUrl = '/view/' . rawurlencode($r['root_name']) . '/' . rawurlencode($relativePath);
            ?>
            <li class="border-b border-gray-100 dark:border-gray-800 pb-3">
                <a href="<?= htmlspecialchars($viewUrl) ?>" class="block">
                    <div class="text-sm font-medium text-blue-700 dark:text-blue-400">
                        <?= htmlspecialchars($r['title']) ?>
                    </div>
                    <div class="text-xs text-gray-400 dark:text-gray-500">
                        <?= htmlspecialchars($displayRoot) ?> / <?= htmlspecialchars($relativePath) ?>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        <?= $r['snippet'] ?>
                    </p>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-400 dark:text-gray-500">
            Index may not reflect files edited outside the app. 
            <a href="/api/search?q=<?= urlencode($q) ?>" class="underline">Raw JSON</a> · 
            <span class="cursor-pointer underline" onclick="rebuildIndex()">Rebuild index</span>
        </p>
    </div>
<?php endif; ?>

<script>
async function rebuildIndex() {
    const el = event.target;
    el.textContent = 'Rebuilding…';
    el.style.cursor = 'wait';
    try {
        const resp = await fetch('/api/search?q=<?= urlencode($q) ?>&_rebuild=1');
        if (resp.ok) {
            location.reload();
        }
    } catch(e) {
        el.textContent = 'Failed — try CLI: php scripts/indexer.php rebuild';
        el.style.color = 'red';
    }
}
</script>
