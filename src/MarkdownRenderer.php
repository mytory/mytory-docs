<?php

declare(strict_types=1);

/**
 * Markdown to HTML rendering using league/commonmark.
 * Handles footnotes, tables, strikethrough, task lists, autolinks,
 * and image path rewriting for local file proxy.
 */

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownRenderer
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new FootnoteExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Render markdown to HTML, rewriting image paths to use the image proxy.
     */
    public function render(string $markdown, string $proxyBasePath): string
    {
        $html = $this->converter->convert($markdown)->getContent();

        // Rewrite relative image paths to use RESTful proxy URL
        // e.g., images/photo.jpg → /image/{root}/{relative_path}/images/photo.jpg
        $encodedBase = implode('/', array_map('rawurlencode', explode('/', $proxyBasePath)));

        $html = preg_replace_callback(
            '/<img\s+[^>]*src="(?!https?:\/\/)([^"]+)"/i',
            function ($matches) use ($encodedBase) {
                $imgPath = ltrim($matches[1], './');
                return str_replace(
                    'src="' . $matches[1] . '"',
                    'src="/image/' . $encodedBase . '/' . $imgPath . '"',
                    $matches[0]
                );
            },
            $html
        );

        // Add table class for Tailwind styling
        $html = str_replace('<table>', '<table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">', $html);
        $html = str_replace('<th>', '<th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700">', $html);
        $html = str_replace('<td>', '<td class="border border-gray-300 dark:border-gray-600 px-4 py-2">', $html);

        return $html;
    }
}
