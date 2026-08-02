<!-- Search: two-step results — 1) filename in current dir, 2) full corpus via index -->

<h1 class="text-xl font-bold mb-2">Search</h1>

<form action="/search" method="get" class="mb-6">
    <div class="flex gap-2">
        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search documents…" autofocus
            class="flex-1 px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
        
        <?php if (!empty($dir)): ?>
        <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
        <?php endif; ?>

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

    <!-- Step 1: filename matches in the current directory (no index) -->
    <?php if (!empty($dir)): 
        $dirSegs = explode('/', $dir, 2);
        $dirRoot = $dirSegs[0];
        $dirRel = $dirSegs[1] ?? '';
    ?>
    <section class="mb-8">
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            현재 폴더에서 파일명 매치
            <span class="font-normal text-gray-400 dark:text-gray-500">(<?= htmlspecialchars($dir) ?>) — <?= count($dirResults) ?>개</span>
        </h2>

        <?php if (empty($dirResults)): ?>
            <p class="text-sm text-gray-400 dark:text-gray-500">파일명에 "<?= htmlspecialchars($q) ?>"이(가) 포함된 파일이 없습니다.</p>
        <?php else: ?>
            <ul class="border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-100 dark:divide-gray-800">
                <?php foreach ($dirResults as $file): 
                    $segments = array_merge([$dirRoot], $dirRel ? explode('/', $dirRel) : [], [$file['path']]);
                    $encodedPath = implode('/', array_map('rawurlencode', $segments));
                ?>
                <li class="px-3 py-2 flex items-center justify-between gap-3">
                    <a href="/view/<?= $encodedPath ?>" class="flex items-center gap-2 text-sm text-gray-900 dark:text-gray-100 hover:text-blue-700 dark:hover:text-blue-400 min-w-0">
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="truncate"><?= htmlspecialchars($file['title']) ?></span>
                    </a>
                    <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= htmlspecialchars($file['date']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">파일명 기준, 하위 폴더 제외</p>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- Step 2: full-corpus search via the FTS5 index -->
    <section>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            전체 문서 검색 (인덱스)
            <span class="font-normal text-gray-400 dark:text-gray-500">— <?= count($results) ?>개</span>
        </h2>

        <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
            인덱스 마지막 갱신: <?= $indexTime ? htmlspecialchars($indexTime) : '없음 (아직 빌드 안 됨)' ?>
            — 앱 밖에서 수정된 파일은 반영되지 않을 수 있음.
            <a href="/api/search?q=<?= urlencode($q) ?><?= !empty($dir) ? '&dir=' . urlencode($dir) : '' ?>" class="underline">Raw JSON</a> · 
            <span class="cursor-pointer underline" onclick="rebuildIndex()">Rebuild index</span>
        </p>

        <?php if (empty($results)): ?>
            <p class="text-sm text-gray-400 dark:text-gray-500 mb-4">No results for "<?= htmlspecialchars($q) ?>"</p>
        <?php else: ?>
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
    </section>
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
