<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FrontMatterTest extends TestCase
{
    public function test_parse_simple_key_value(): void
    {
        $content = "---\ntitle: Hello World\ndate: 2024-01-15\n---\n\n# Body text";
        $meta = FrontMatter::parse($content);

        $this->assertSame('Hello World', $meta['title']);
        $this->assertSame('2024-01-15', $meta['date']);
        $this->assertStringContainsString('# Body text', $content);
    }

    public function test_parse_array_value(): void
    {
        $content = "---\ntags: [react, javascript, frontend]\n---\n\nContent";
        $meta = FrontMatter::parse($content);

        $this->assertIsArray($meta['tags']);
        $this->assertCount(3, $meta['tags']);
        $this->assertSame('react', $meta['tags'][0]);
        $this->assertSame('javascript', $meta['tags'][1]);
    }

    public function test_parse_multiline_list(): void
    {
        $content = "---\ncategories:\n  - dev\n  - writing\n  - notes\n---\n\nContent";
        $meta = FrontMatter::parse($content);

        $this->assertIsArray($meta['categories']);
        $this->assertCount(3, $meta['categories']);
        $this->assertSame('dev', $meta['categories'][0]);
        $this->assertSame('writing', $meta['categories'][1]);
    }

    public function test_parse_without_front_matter(): void
    {
        $content = "# Just a heading\n\nNo front matter here.";
        $meta = FrontMatter::parse($content);

        $this->assertEmpty($meta);
        $this->assertStringContainsString('# Just a heading', $content);
    }

    public function test_parse_with_hyphenated_key(): void
    {
        $content = "---\nlast-modified: 2024-01-15\n---\n\nContent";
        $meta = FrontMatter::parse($content);

        $this->assertSame('2024-01-15', $meta['last-modified']);
    }

    public function test_parse_with_empty_value(): void
    {
        $content = "---\ndraft: \npublished: false\n---\n\nContent";
        $meta = FrontMatter::parse($content);

        $this->assertSame('', $meta['draft']);
        $this->assertSame('false', $meta['published']);
    }

    public function test_parse_removes_front_matter_from_content(): void
    {
        $content = "---\ntitle: Test\n---\n\n# Real Content\n\nParagraph.";
        $meta = FrontMatter::parse($content);

        $this->assertSame('Test', $meta['title']);
        $this->assertStringNotContainsString('---', $content);
        $this->assertStringContainsString('# Real Content', $content);
        $this->assertStringContainsString('Paragraph.', $content);
    }

    public function test_parse_with_spaces_around_colon(): void
    {
        $content = "---\ntitle : Spaced Title\ndate:   2024-01-15\n---\n\nContent";
        $meta = FrontMatter::parse($content);

        $this->assertSame('Spaced Title', $meta['title']);
        $this->assertSame('2024-01-15', $meta['date']);
    }

    public function test_parse_content_with_dashes_but_not_front_matter(): void
    {
        $content = "Some text\n---\nMore text\n---\nEven more";
        $meta = FrontMatter::parse($content);

        // No leading --- block, so no front matter
        $this->assertEmpty($meta);
    }

    public function test_parse_single_line_breaks(): void
    {
        $content = "---\ntitle: Title\n---\nBody";
        $meta = FrontMatter::parse($content);

        $this->assertSame('Title', $meta['title']);
        $this->assertSame('Body', trim($content));
    }
}
