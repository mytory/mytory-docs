<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PathParserTest extends TestCase
{
    public function test_parse_view_path_with_file(): void
    {
        $result = PathParser::parse('view:test/notes/react.md');

        $this->assertSame('view', $result['cmd']);
        $this->assertSame('test', $result['root_name']);
        $this->assertSame('notes', $result['relative_path']);
        $this->assertSame('react.md', $result['file']);
        $this->assertSame('test/notes', $result['full_path']);
    }

    public function test_parse_list_root_path(): void
    {
        $result = PathParser::parse('list:test');

        $this->assertSame('list', $result['cmd']);
        $this->assertSame('test', $result['root_name']);
        $this->assertSame('', $result['relative_path']);
        $this->assertSame('', $result['file']);
        $this->assertSame('test', $result['full_path']);
    }

    public function test_parse_view_path_without_file(): void
    {
        $result = PathParser::parse('view:test/notes');

        $this->assertSame('view', $result['cmd']);
        $this->assertSame('test', $result['root_name']);
        $this->assertSame('notes', $result['relative_path']);
        $this->assertSame('', $result['file']);
        $this->assertSame('test/notes', $result['full_path']);
    }

    public function test_parse_edit_path(): void
    {
        $result = PathParser::parse('edit:test/notes/react.md');

        $this->assertSame('edit', $result['cmd']);
        $this->assertSame('test', $result['root_name']);
        $this->assertSame('notes', $result['relative_path']);
        $this->assertSame('react.md', $result['file']);
    }

    public function test_parse_deeply_nested_path(): void
    {
        $result = PathParser::parse('view:test/a/b/c/d/e.md');

        $this->assertSame('a/b/c/d', $result['relative_path']);
        $this->assertSame('e.md', $result['file']);
    }

    public function test_parse_unknown_root_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown doc_root');

        PathParser::parse('view:unknown_root/file.md');
    }

    public function test_get_cmd(): void
    {
        $this->assertSame('view', PathParser::getCmd('view:test/file.md'));
        $this->assertSame('edit', PathParser::getCmd('edit:test/file.md'));
        $this->assertSame('list', PathParser::getCmd('list:test'));
    }

    public function test_convert_from_os_encoding_utf8_passthrough(): void
    {
        $korean = '한글파일.md';
        $result = PathParser::convertFromOsEncoding($korean);
        $this->assertSame($korean, $result);
    }

    public function test_real_full_path_is_absolute(): void
    {
        $result = PathParser::parse('list:test');
        $this->assertStringStartsWith('/', $result['root_path']);
        $this->assertDirectoryExists($result['root_path']);
    }
}
