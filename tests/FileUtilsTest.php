<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FileUtilsTest extends TestCase
{
    private string $fixtureDir;
    private string $docsRoot;

    protected function setUp(): void
    {
        global $doc_roots;
        $this->docsRoot = $doc_roots['test'];
        $this->fixtureDir = $this->docsRoot . '/fixtures';
        if (!is_dir($this->fixtureDir)) {
            mkdir($this->fixtureDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        array_map('unlink', glob($this->fixtureDir . '/*') ?: []);
        rmdir($this->fixtureDir);
    }

    // ── readContent ──────────────────────────────────────

    public function test_read_content_utf8(): void
    {
        $path = $this->fixtureDir . '/test.md';
        file_put_contents($path, "# Hello\n\nWorld");
        $content = FileUtils::readContent($path);

        $this->assertStringContainsString('# Hello', $content);
        $this->assertStringContainsString('World', $content);
    }

    public function test_read_content_normalizes_line_endings(): void
    {
        $path = $this->fixtureDir . '/crlf.md';
        file_put_contents($path, "line1\r\nline2\r\nline3");
        $content = FileUtils::readContent($path);

        $this->assertStringNotContainsString("\r", $content);
        $this->assertStringContainsString("line1\nline2\nline3", $content);
    }

    public function test_read_nonexistent_file_throws(): void
    {
        $this->expectException(RuntimeException::class);
        FileUtils::readContent($this->fixtureDir . '/nonexistent.md');
    }

    // ── writeContent ─────────────────────────────────────

    public function test_write_content_returns_mtime(): void
    {
        $path = $this->fixtureDir . '/write-test.md';
        $mtime = FileUtils::writeContent($path, 'New content');

        $this->assertIsInt($mtime);
        $this->assertGreaterThan(0, $mtime);
        $this->assertFileExists($path);
        $this->assertSame('New content', file_get_contents($path));
    }

    public function test_write_content_overwrites(): void
    {
        $path = $this->fixtureDir . '/overwrite.md';
        file_put_contents($path, 'original');
        FileUtils::writeContent($path, 'updated');

        $this->assertSame('updated', file_get_contents($path));
    }

    // ── createFile ────────────────────────────────────────

    public function test_create_file_with_default_heading(): void
    {
        $newPath = FileUtils::createFile($this->fixtureDir, 'new-note.md');

        $this->assertFileExists($newPath);
        $this->assertStringContainsString('# new-note', file_get_contents($newPath));
    }

    public function test_create_file_adds_default_extension(): void
    {
        $newPath = FileUtils::createFile($this->fixtureDir, 'noextension');

        $this->assertStringEndsWith('.md', $newPath);
        $this->assertFileExists($newPath);
    }

    public function test_create_duplicate_file_throws(): void
    {
        FileUtils::createFile($this->fixtureDir, 'dup.md');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already exists');
        FileUtils::createFile($this->fixtureDir, 'dup.md');
    }

    // ── deleteFile ────────────────────────────────────────

    public function test_delete_file(): void
    {
        $path = $this->fixtureDir . '/to-delete.md';
        file_put_contents($path, 'delete me');
        FileUtils::deleteFile($path);

        $this->assertFileDoesNotExist($path);
    }

    public function test_delete_nonexistent_file_throws(): void
    {
        $this->expectException(RuntimeException::class);
        FileUtils::deleteFile($this->fixtureDir . '/nonexistent.md');
    }

    // ── extractDate ──────────────────────────────────────

    public function test_extract_date_from_front_matter(): void
    {
        $path = $this->fixtureDir . '/dated.md';
        file_put_contents($path, "---\ndate: 2024-03-15\n---\n\nContent");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2024-03-15', $date);
    }

    public function test_extract_date_from_list_item_under_title(): void
    {
        $path = $this->fixtureDir . '/inline-date.md';
        file_put_contents($path, "# Title\n\n- date: 2023-12-25\n- tag: x\n\nContent");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2023-12-25', $date);
    }

    public function test_extract_date_standalone_under_title(): void
    {
        $path = $this->fixtureDir . '/standalone-date.md';
        file_put_contents($path, "# Title\n\n2026-03-05\n\n본문");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2026-03-05', $date);
    }

    public function test_extract_date_korean_list_item_with_space(): void
    {
        $path = $this->fixtureDir . '/korean-date.md';
        file_put_contents($path, "# Title\n\n* 날짜 : 2026-03-05\n* 발신 : 웹개발팀");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2026-03-05', $date);
    }

    public function test_extract_date_from_list_item_with_time_value(): void
    {
        $path = $this->fixtureDir . '/dated-time.md';
        file_put_contents($path, "# Title\n\n- Date: 2012-11-29 12:00:00");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2012-11-29', $date);
    }

    public function test_extract_date_from_setext_heading(): void
    {
        $path = $this->fixtureDir . '/setext-date.md';
        file_put_contents($path, "Title\n======\n\n- Date: 2024-01-01");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2024-01-01', $date);
    }

    public function test_extract_date_ignores_body_date(): void
    {
        $path = $this->fixtureDir . '/body-date.md';
        file_put_contents($path, "# Title\n\n본문 내용입니다.\n\n2026-03-05\n\n마지막");
        $date = FileUtils::extractDate($path);

        // Body-text date must NOT be used; falls back to ctime
        $this->assertNotSame('2026-03-05', $date);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
    }

    public function test_extract_date_korean_title_with_0x85_byte(): void
    {
        // `테` (U+D14C, UTF-8 ED 85 8C) contains a 0x85 byte that \R without
        // the `u` flag mistakes for a NEL newline. The title must not split.
        $path = $this->fixtureDir . '/korean-title.md';
        file_put_contents($path, "# OCM 테이블 설계\n\n2025-03-19\n\n본문");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2025-03-19', $date);
    }

    public function test_extract_date_list_at_top_without_heading(): void
    {
        // Legacy docs without a heading declare `- Date:` on the first line.
        $path = $this->fixtureDir . '/no-heading.md';
        file_put_contents($path, "- Date: 2013-09-02 16:14:31\n\n본문 시작");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2013-09-02', $date);
    }

    public function test_extract_date_falls_back_to_filectime(): void
    {
        $path = $this->fixtureDir . '/nodate.md';
        file_put_contents($path, "# No date here\nJust content");
        $date = FileUtils::extractDate($path);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
    }

    public function test_extract_date_from_filename_prefix(): void
    {
        $path = $this->fixtureDir . '/2024-05-20_meeting-notes.md';
        file_put_contents($path, "# No date in content");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2024-05-20', $date);
    }

    public function test_extract_date_content_wins_over_filename(): void
    {
        $path = $this->fixtureDir . '/2024-05-20_meeting-notes.md';
        file_put_contents($path, "---\ndate: 2023-01-02\n---\n\n# Content");
        $date = FileUtils::extractDate($path);

        $this->assertSame('2023-01-02', $date);
    }

    public function test_extract_date_from_filename_non_markdown(): void
    {
        $path = $this->fixtureDir . '/2024-05-20_screenshot.jpg';
        file_put_contents($path, 'fake image');
        $date = FileUtils::extractDate($path);

        $this->assertSame('2024-05-20', $date);
    }

    // ── extractTitle ─────────────────────────────────────

    public function test_extract_title_from_front_matter(): void
    {
        $path = $this->fixtureDir . '/titled.md';
        file_put_contents($path, "---\ntitle: Front Matter Title\n---\n\n# Heading Title");
        $title = FileUtils::extractTitle($path);

        $this->assertSame('Front Matter Title', $title);
    }

    public function test_extract_title_from_heading(): void
    {
        $path = $this->fixtureDir . '/heading-title.md';
        file_put_contents($path, "# First Heading Title\n\nContent");
        $title = FileUtils::extractTitle($path);

        $this->assertSame('First Heading Title', $title);
    }

    public function test_extract_title_from_filename_fallback(): void
    {
        $path = $this->fixtureDir . '/notitle.md';
        file_put_contents($path, "Just some random content.");
        $title = FileUtils::extractTitle($path);

        // Should return the filename
        $this->assertStringContainsString('notitle.md', $title);
    }

    // ── isMarkdownFile ────────────────────────────────────

    public function test_is_markdown_file(): void
    {
        $this->assertTrue(FileUtils::isMarkdownFile('/path/to/file.md'));
        $this->assertTrue(FileUtils::isMarkdownFile('/path/to/file.txt'));
        $this->assertFalse(FileUtils::isMarkdownFile('/path/to/file.jpg'));
        $this->assertFalse(FileUtils::isMarkdownFile('/path/to/file.pdf'));
    }

    // ── listDirectory ─────────────────────────────────────

    public function test_list_directory(): void
    {
        // Create test structure
        mkdir($this->fixtureDir . '/subdir');
        file_put_contents($this->fixtureDir . '/a.md', "# A\n\ndate: 2024-01-01\nContent");
        file_put_contents($this->fixtureDir . '/b.md', "# B\n\ndate: 2024-06-01\nContent");
        file_put_contents($this->fixtureDir . '/image.jpg', 'fake image');

        $listing = FileUtils::listDirectory($this->fixtureDir);

        // Dirs listed
        $this->assertCount(1, $listing['dirs']);
        $this->assertSame('subdir', $listing['dirs'][0]['name']);

        // Files listed (should be 3, or at least the md files)
        $this->assertGreaterThanOrEqual(2, count($listing['files']));

        // Files sorted by date descending
        $dates = array_column($listing['files'], 'date');
        $sorted = $dates;
        usort($sorted, fn($a, $b) => strcmp($b, $a));
        $this->assertSame($sorted, $dates, 'Files should be sorted by date descending');

        // Clean up subdir
        rmdir($this->fixtureDir . '/subdir');
    }

    public function test_list_nonexistent_directory_throws(): void
    {
        $this->expectException(RuntimeException::class);
        FileUtils::listDirectory('/nonexistent/path');
    }

    // ── filterFilenames ────────────────────────────────────

    public function test_filter_filenames_case_insensitive(): void
    {
        file_put_contents($this->fixtureDir . '/react-hooks.md', '# React');
        file_put_contents($this->fixtureDir . '/ReactGuide.md', '# React Guide');
        file_put_contents($this->fixtureDir . '/vue.md', '# Vue');

        $matches = FileUtils::filterFilenames($this->fixtureDir, 'react');
        $names = array_column($matches, 'name');

        $this->assertCount(2, $names);
        $this->assertContains('react-hooks.md', $names);
        $this->assertContains('ReactGuide.md', $names);

        // Uppercase query matches too
        $matchesUpper = FileUtils::filterFilenames($this->fixtureDir, 'REACT');
        $this->assertCount(2, $matchesUpper);
    }

    public function test_filter_filenames_ignores_subdirectories(): void
    {
        mkdir($this->fixtureDir . '/react-projects');
        file_put_contents($this->fixtureDir . '/react-notes.md', '# React Notes');

        $matches = FileUtils::filterFilenames($this->fixtureDir, 'react');
        $names = array_column($matches, 'name');

        $this->assertCount(1, $names);
        $this->assertSame(['react-notes.md'], $names);

        rmdir($this->fixtureDir . '/react-projects');
    }

    public function test_filter_filenames_empty_query_returns_empty(): void
    {
        file_put_contents($this->fixtureDir . '/a.md', '# A');
        $this->assertSame([], FileUtils::filterFilenames($this->fixtureDir, ''));
        $this->assertSame([], FileUtils::filterFilenames($this->fixtureDir, '   '));
    }

    public function test_filter_filenames_respects_limit(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            file_put_contents($this->fixtureDir . "/doc-{$i}.md", "# Doc {$i}");
        }

        $matches = FileUtils::filterFilenames($this->fixtureDir, 'doc', 2);

        $this->assertCount(2, $matches);
    }

    public function test_filter_filenames_nonexistent_dir_returns_empty(): void
    {
        $this->assertSame([], FileUtils::filterFilenames('/nonexistent/path', 'doc'));
    }
}
