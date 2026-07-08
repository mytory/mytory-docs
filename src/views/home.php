<!-- Home: doc_roots list -->
<h1 class="text-xl font-bold mb-6">Mytory Docs</h1>
<ul class="space-y-1">
    <?php foreach ($doc_roots as $name => $dir): ?>
        <li>
            <a href="/list/<?= rawurlencode(PathParser::convertFromOsEncoding($name)) ?>" 
               class="flex items-center gap-2 py-1.5 px-2 -mx-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-blue-700 dark:text-blue-400">
                <svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                </svg>
                <?= htmlspecialchars(PathParser::convertFromOsEncoding($name)) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
