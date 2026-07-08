<!-- List: directory listing -->

<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
        </svg>
        <h1 class="text-lg font-semibold"><?= htmlspecialchars($pageTitle) ?></h1>
    </div>
    <button onclick="document.getElementById('new-file-dialog').showModal()" 
        class="text-sm px-2 py-1 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800">
        + New File
    </button>
</div>

<table class="w-full text-sm">
    <thead>
        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 text-left">
            <th class="pb-2 font-normal">Name</th>
            <th class="pb-2 font-normal w-28">Date</th>
            <th class="pb-2 font-normal w-8"></th>
        </tr>
    </thead>
    <tbody>
        <!-- Parent folder -->
        <?php if ($parentPath !== null): ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
            <td class="py-1.5">
                <a href="<?= htmlspecialchars($parentPath) ?>" class="flex items-center gap-2 text-blue-700 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                    Parent Folder
                </a>
            </td>
            <td></td>
            <td></td>
        </tr>
        <?php endif; ?>

        <!-- Ensure Root Folder link when at root level -->
        <?php if ($parentPath === null && !empty($parsed['root_name'])): ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
            <td class="py-1.5">
                <a href="/" class="flex items-center gap-2 text-blue-700 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                    Root Folder
                </a>
            </td>
            <td></td>
            <td></td>
        </tr>
        <?php endif; ?>

        <!-- Directories -->
        <?php foreach ($listing['dirs'] as $dir): ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
            <td class="py-1.5">
                <a href="/list/<?= rawurlencode($parsed['root_name'] . '/' . $parsed['relative_path'] . ($parsed['relative_path'] ? '/' : '') . $dir['path']) ?>" 
                   class="flex items-center gap-2 text-blue-700 dark:text-blue-400">
                    <svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                    </svg>
                    <?= htmlspecialchars($dir['name']) ?>
                </a>
            </td>
            <td></td>
            <td></td>
        </tr>
        <?php endforeach; ?>

        <!-- Files -->
        <?php foreach ($listing['files'] as $file): 
            $viewPath = $parsed['root_name'] . '/' . $parsed['relative_path'] . ($parsed['relative_path'] ? '/' : '') . $file['path'];
        ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
            <td class="py-1.5">
                <?php if ($file['markdown']): ?>
                    <?php 
                $segments = explode('/', $viewPath);
                $encodedPath = implode('/', array_map('rawurlencode', $segments));
            ?>
            <a href="/view/<?= $encodedPath ?>" class="flex items-center gap-2 text-gray-900 dark:text-gray-100 hover:text-blue-700 dark:hover:text-blue-400">
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        <?= $file['title'] ?>
                    </a>
                <?php else: ?>
                    <a href="#" onclick="prompt('Full path:', '<?= htmlspecialchars($file['name']) ?>'); return false;" 
                       class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                        </svg>
                        <?= $file['title'] ?>
                    </a>
                <?php endif; ?>
            </td>
            <td class="py-1.5 text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= htmlspecialchars($file['date']) ?></td>
            <td class="py-1.5">
                <button onclick="deleteFile('<?= rawurlencode($viewPath) ?>', '<?= htmlspecialchars(addslashes(strip_tags($file['title']))) ?>')" 
                    class="text-gray-400 hover:text-red-500" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
function deleteFile(path, title) {
    document.getElementById('delete-dialog-message').textContent = 'Delete: ' + title;
    document.getElementById('delete-dialog-form').action = '/delete-file/' + path;
    document.getElementById('delete-dialog').showModal();
}
</script>
