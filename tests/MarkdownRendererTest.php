<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MarkdownRendererTest extends TestCase
{
    private MarkdownRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new MarkdownRenderer();
    }

    // ── Basic rendering ───────────────────────────────────

    public function test_renders_headings(): void
    {
        $html = $this->renderer->render('# Hello World', 'test/notes');

        $this->assertStringContainsString('<h1>', $html);
        $this->assertStringContainsString('Hello World', $html);
    }

    public function test_renders_paragraphs(): void
    {
        $html = $this->renderer->render("Paragraph text.", 'test/notes');

        $this->assertStringContainsString('<p>', $html);
        $this->assertStringContainsString('Paragraph text.', $html);
    }

    public function test_renders_bold_and_italic(): void
    {
        $html = $this->renderer->render('**bold** and *italic*', 'test/notes');

        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<em>italic</em>', $html);
    }

    public function test_renders_code_blocks(): void
    {
        $html = $this->renderer->render("`inline code`", 'test/notes');

        $this->assertStringContainsString('<code>', $html);
        $this->assertStringContainsString('inline code', $html);
    }

    public function test_renders_fenced_code_blocks(): void
    {
        $markdown = "```php\necho 'hello';\n```";
        $html = $this->renderer->render($markdown, 'test/notes');

        $this->assertStringContainsString('<pre>', $html);
        $this->assertStringContainsString('<code', $html);
    }

    public function test_renders_unordered_lists(): void
    {
        $html = $this->renderer->render("- item 1\n- item 2", 'test/notes');

        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('<li>', $html);
    }

    public function test_renders_links(): void
    {
        $html = $this->renderer->render('[Google](https://google.com)', 'test/notes');

        $this->assertStringContainsString('<a href="https://google.com"', $html);
        $this->assertStringContainsString('Google', $html);
    }

    public function test_renders_tables(): void
    {
        $markdown = "| A | B |\n|---|---|\n| 1 | 2 |";
        $html = $this->renderer->render($markdown, 'test/notes');

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<th', $html);
        $this->assertStringContainsString('<td', $html);
    }

    public function test_does_not_render_tilde_as_strikethrough(): void
    {
        // ~ is commonly used as range indicator in Korean (e.g., "10월~12월")
        $html = $this->renderer->render('2024년 10월~2025년 5월 8개월과 2025년 6월~2026', 'test/notes');

        $this->assertStringNotContainsString('<del>', $html);
        $this->assertStringContainsString('10월~2025', $html);
    }

    public function test_renders_task_lists(): void
    {
        $html = $this->renderer->render("- [x] Done\n- [ ] Not done", 'test/notes');

        $this->assertStringContainsString('checkbox', $html);
    }

    // ── Image path rewriting ──────────────────────────────

    public function test_rewrites_relative_image_paths(): void
    {
        $html = $this->renderer->render('![](images/photo.jpg)', 'test/notes');

        $this->assertStringContainsString('/image/test/notes/images/photo.jpg', $html);
    }

    public function test_does_not_rewrite_absolute_urls(): void
    {
        $html = $this->renderer->render('![](https://example.com/img.jpg)', 'test/notes');

        $this->assertStringContainsString('https://example.com/img.jpg', $html);
        $this->assertStringNotContainsString('/image/', $html);
    }

    // ── Table styling ─────────────────────────────────────

    public function test_adds_tailwind_classes_to_tables(): void
    {
        $markdown = "| Col |\n|-----|\n| Val |";
        $html = $this->renderer->render($markdown, 'test/notes');

        $this->assertStringContainsString('border-collapse', $html);
        $this->assertStringContainsString('border-gray-300', $html);
    }

    // ── HTML stripping ────────────────────────────────────

    public function test_strips_raw_html(): void
    {
        $html = $this->renderer->render('<script>alert("xss")</script>', 'test/notes');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('alert', $html);
    }

    // ── Autolinks ─────────────────────────────────────────

    public function test_autolinks_urls(): void
    {
        $html = $this->renderer->render('Visit https://example.com', 'test/notes');

        $this->assertStringContainsString('<a href="https://example.com"', $html);
    }

    // ── Numeric cell alignment ───────────────────────────

    public function test_right_aligns_numeric_cells(): void
    {
        $markdown = "| A | B |\n|---|---|\n| 123 | text |\n| 1.87% | word |";
        $html = $this->renderer->render($markdown, 'test/notes');

        $this->assertStringContainsString('text-right', $html);
        $this->assertStringContainsString('tabular-nums', $html);
        $this->assertStringContainsString('font-mono', $html);
    }

    public function test_right_aligns_numeric_cells_with_units_and_ranges(): void
    {
        $markdown = "| A | B | C |\n|---|---|---|\n| 9.3h | 145 (22%) | 10:09 |\n| <12% | 78.7 🥇 | 148분 (최악) |";
        $html = $this->renderer->render($markdown, 'test/notes');

        $this->assertStringContainsString('text-right', $html);
        $this->assertStringContainsString('font-mono', $html);
    }

    public function test_does_not_style_text_cells(): void
    {
        $markdown = "| A |\n|---|\n| 급변 (≥2h) |\n| 42회 (6%) — 밤샘 |";
        $html = $this->renderer->render($markdown, 'test/notes');

        // 괄호 밖에 주석이 남아 있어 숫자 셀로 보지 않는다
        $this->assertStringNotContainsString('font-mono', $html);
    }

    // ── Footnotes ─────────────────────────────────────────

    public function test_renders_footnotes(): void
    {
        $markdown = "Text with footnote[^1].\n\n[^1]: This is the footnote.";
        $html = $this->renderer->render($markdown, 'test/notes');

        $this->assertStringContainsString('footnote', $html);
        $this->assertStringContainsString('This is the footnote', $html);
    }

    // ── Empty content ─────────────────────────────────────

    public function test_renders_empty_content(): void
    {
        $html = $this->renderer->render('', 'test/notes');

        // Should return empty or minimal content, not crash
        $this->assertIsString($html);
    }
}
