<!-- View: markdown document -->
<div>
<?php if (!empty($frontMatter['title'])): ?>
    <h1 class="text-2xl font-bold mb-6"><?= htmlspecialchars((string)$frontMatter['title']) ?></h1>
<?php endif; ?>

<?php
// Show remaining front matter metadata
$meta = $frontMatter;
unset($meta['title']);
if (count($meta) > 0):
?>
    <div class="mb-6 p-3 bg-gray-50 dark:bg-gray-800 rounded text-sm">
        <dl class="space-y-1">
            <?php foreach ($meta as $key => $val): ?>
                <div class="flex gap-2">
                    <dt class="font-medium text-gray-500 dark:text-gray-400"><?= htmlspecialchars((string)$key) ?>:</dt>
                    <dd>
                        <?php if (is_array($val)): ?>
                            <?= htmlspecialchars(implode(', ', $val)) ?>
                        <?php else: ?>
                            <?= htmlspecialchars((string)$val) ?>
                        <?php endif; ?>
                    </dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>
<?php endif; ?>

<div class="prose dark:prose-invert max-w-none">
    <?= $html ?>
</div>
</div>
</div>
