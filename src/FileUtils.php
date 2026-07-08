<?php

declare(strict_types=1);

/**
 * File utility functions: encoding, date extraction, file type detection,
 * directory listing, file creation/deletion.
 */

class FileUtils
{
    /**
     * Read file content, converting from EUC-KR to UTF-8 if needed.
     */
    public static function readContent(string $filePath): string
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Cannot read file: {$filePath}");
        }

        $encoding = mb_detect_encoding($content, ['UTF-8', 'EUC-KR', 'CP949', 'ASCII'], true);
        if (in_array($encoding, ['EUC-KR', 'CP949'], true)) {
            $content = iconv((string)$encoding, 'UTF-8//IGNORE', $content) ?: $content;
        }

        return str_replace(["\r\n", "\r"], "\n", $content);
    }

    /**
     * Write file content. Returns the new mtime for conflict detection.
     */
    public static function writeContent(string $filePath, string $content): int
    {
        $handle = fopen($filePath, 'w');
        if (!$handle) {
            throw new \RuntimeException("Cannot open for writing: {$filePath}");
        }
        if (fwrite($handle, $content) === false) {
            fclose($handle);
            throw new \RuntimeException("Cannot write to: {$filePath}");
        }
        fclose($handle);

        $now = time();
        touch($filePath, $now);
        return $now;
    }

    /**
     * Create a new markdown file with a default heading.
     */
    public static function createFile(string $dirPath, string $filename): string
    {
        global $markdown_ext_list;

        $pathinfo = pathinfo($filename);
        if (!in_array($pathinfo['extension'] ?? '', $markdown_ext_list, true)) {
            $filename .= '.' . ($markdown_ext_list[0] ?? 'md');
        }

        $newFile = $dirPath . DIRECTORY_SEPARATOR . $filename;
        if (is_file($newFile)) {
            throw new \RuntimeException('File already exists');
        }

        $title = $pathinfo['filename'];
        file_put_contents($newFile, '# ' . $title);

        return $newFile;
    }

    /**
     * Delete a file.
     */
    public static function deleteFile(string $filePath): void
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }
        unlink($filePath);
    }

    /**
     * Extract date from file content (YAML front matter fields or inline patterns).
     */
    public static function extractDate(string $filePath): ?string
    {
        global $markdown_ext_list;

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Non-text files: try date from filename or filectime
        if (!in_array($ext, $markdown_ext_list, true)) {
            $filename = PathParser::convertFromOsEncoding(pathinfo($filePath, PATHINFO_FILENAME));
            if (strtotime(substr($filename, 0, 10))) {
                return date('Y-m-d', strtotime(substr($filename, 0, 10)));
            }
            return date('Y-m-d', filectime($filePath));
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return date('Y-m-d', filectime($filePath));
        }

        // Try YAML front matter date first
        $frontMatter = FrontMatter::parse($content);
        if (!empty($frontMatter['date'])) {
            $date = date_parse((string)$frontMatter['date']);
            if ($date['year'] && $date['month'] && $date['day']) {
                return sprintf('%04d-%02d-%02d', $date['year'], $date['month'], $date['day']);
            }
        }

        // Try inline patterns (date: YYYY-MM-DD or 날짜: YYYY-MM-DD)
        $patterns = [
            '/[Dd]ate\s*:\s*(\d{4}-\d{2}-\d{2})/',
            '/날짜\s*:\s*(\d{4}-\d{2}-\d{2})/',
            '/일시\s*:\s*(\d{4}-\d{2}-\d{2})/',
            '/\n(\d{4}-\d{2}-\d{2})\s/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $m)) {
                return trim($m[1]);
            }
        }

        return date('Y-m-d', filectime($filePath));
    }

    /**
     * Extract display title from a markdown file: YAML front matter title, # heading, or filename.
     */
    public static function extractTitle(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $filename = PathParser::convertFromOsEncoding(pathinfo($filePath, PATHINFO_BASENAME));

        if (!in_array($ext, ['md', 'txt'], true)) {
            // Non-text: try date prefix removal
            if (strtotime(substr(basename($filePath, '.' . $ext), 0, 10))) {
                $title = trim(substr(basename($filePath, '.' . $ext), 10));
                return "<small><span class='extension-badge'>" . strtoupper($ext) . "</span></small> {$title}";
            }
            return $filename;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return $filename;
        }

        // Front matter title takes priority
        $frontMatter = FrontMatter::parse($content);
        if (!empty($frontMatter['title'])) {
            return (string)$frontMatter['title'];
        }

        // First # heading
        $encoding = mb_detect_encoding($content, ['UTF-8', 'EUC-KR'], true);
        if ($encoding === 'EUC-KR') {
            $content = iconv('EUC-KR', 'UTF-8//IGNORE', $content) ?: $content;
        }

        if (preg_match('/^#\s*(.+)$/m', $content, $m)) {
            return trim($m[1]);
        }
        // Setext heading (underlined with === or ---)
        if (preg_match('/^(.+)\n[=]{3,}$/m', $content, $m)) {
            return trim($m[1]);
        }

        return urldecode($filename);
    }

    /**
     * Check if a file has a markdown-renderable extension.
     */
    public static function isMarkdownFile(string $path): bool
    {
        global $markdown_ext_list;
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $markdown_ext_list, true);
    }

    /**
     * List directory contents, sorted: directories first (alphabetical), files (by date desc).
     * 
     * @return array{dirs: array, files: array}
     */
    public static function listDirectory(string $dirPath): array
    {
        if (!is_dir($dirPath)) {
            throw new \RuntimeException("Not a directory: {$dirPath}");
        }

        $dirs = [];
        $files = [];
        $handle = opendir($dirPath);

        if ($handle === false) {
            throw new \RuntimeException("Cannot open directory: {$dirPath}");
        }

        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }
            $fullPath = $dirPath . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($fullPath)) {
                $dirs[] = [
                    'name' => PathParser::convertFromOsEncoding($entry),
                    'path' => $entry,
                ];
            } elseif (is_file($fullPath)) {
                $files[] = [
                    'name'     => PathParser::convertFromOsEncoding($entry),
                    'path'     => $entry,
                    'title'    => self::extractTitle($fullPath),
                    'date'     => self::extractDate($fullPath),
                    'markdown' => self::isMarkdownFile($fullPath),
                ];
            }
        }
        closedir($handle);

        // Sort dirs alphabetically
        usort($dirs, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        // Sort files by date descending
        usort($files, fn($a, $b) => strcmp($b['date'], $a['date']));

        return ['dirs' => $dirs, 'files' => $files];
    }
}
