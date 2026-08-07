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

        // Add table classes + right-align numeric cells
        $html = preg_replace('/<table>/', '<table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">', $html);
        $html = preg_replace('/<th(\s|>)/', '<th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700"$1', $html);
        $html = preg_replace_callback(
            '/<td(\s[^>]*)?>(.*?)<\/td>/s',
            function ($m) {
                $attrs = $m[1] ?? '';
                $content = $m[2];
                $isNumeric = self::isNumericCell($content);
                $extra = $isNumeric ? ' text-right tabular-nums font-mono' : '';
                return "<td class=\"border border-gray-300 dark:border-gray-600 px-4 py-2{$extra}\"{$attrs}>{$content}</td>";
            },
            $html
        );

        return $html;
    }

    /**
     * 셀의 실질 내용이 숫자(단위·범위 포함)인지 판별한다.
     *
     * - HTML 엔티티를 디코딩해 &lt;12% → <12% 로 만든다.
     * - 괄호 안 주석은 제거한다: (22%), (최악), (중앙 03:17)
     * - 이모지(🥇 등)를 제거한다.
     * - 끝의 단위 접미사(h, 분, 회, 시, +)를 제거한다.
     * - 남은 텍스트가 숫자·구분자·범위기호뿐이면 숫자 셀로 본다.
     */
    private static function isNumericCell(string $content): bool
    {
        $text = trim(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = trim((string) preg_replace('/\([^)]*\)/u', '', $text));
        $text = (string) preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{2B00}-\x{2BFF}]/u', '', $text);
        $text = (string) preg_replace('/[가-힣a-zA-Z]{1,2}\+?$/u', '', $text);
        $text = trim($text);
        if ($text === '') {
            return false;
        }

        return (bool) preg_match('/^[<>≤≥]?[\d,.\s%+\-–—*×~:^]+$/', $text);
    }
}
