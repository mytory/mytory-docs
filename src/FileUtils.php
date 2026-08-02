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
     * Extract date from a file.
     * Priority: document date (front matter / title-adjacent) → filename date prefix → filectime.
     */
    public static function extractDate(string $filePath): ?string
    {
        global $markdown_ext_list;

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Non-text files: try date from filename or filectime
        if (!in_array($ext, $markdown_ext_list, true)) {
            return self::extractDateFromFilename($filePath) ?? date('Y-m-d', filectime($filePath));
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return self::extractDateFromFilename($filePath) ?? date('Y-m-d', filectime($filePath));
        }

        // YAML front matter date takes priority
        $frontMatter = FrontMatter::parse($content);
        if (!empty($frontMatter['date'])) {
            $date = date_parse((string)$frontMatter['date']);
            if ($date['year'] && $date['month'] && $date['day']) {
                return sprintf('%04d-%02d-%02d', $date['year'], $date['month'], $date['day']);
            }
        }

        // Title-adjacent date: a standalone date line or `- {date|날짜|일시}: ...` list
        // item immediately below the first heading. Body-text dates are ignored.
        $titleDate = self::extractDateNearTitle($content);
        if ($titleDate !== null) {
            return $titleDate;
        }

        // Fallback: date prefix in filename (e.g. 2026-08-01_review-...md), then filectime
        return self::extractDateFromFilename($filePath) ?? date('Y-m-d', filectime($filePath));
    }

    /**
     * Extract a date from right below the first heading (the title):
     *  - a standalone YYYY-MM-DD line, or
     *  - a list item `- {date|날짜|일시}: YYYY-MM-DD` in the first list block.
     * Returns null when there is no title-adjacent date.
     */
    private static function extractDateNearTitle(string $content): ?string
    {
        $lines = preg_split('/\R/u', $content);
        $count = count($lines);

        // Locate the first heading line (ATX `# ...` or setext `===`/`---`)
        $titleIdx = -1;
        for ($i = 0; $i < $count; $i++) {
            $line = rtrim($lines[$i]);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^#{1,6}[ \t]+/', $line)) {
                $titleIdx = $i;
                break;
            }
            // Setext: current line is an underline, the line above is the title
            if ($i > 0 && preg_match('/^={3,}$/', trim($line))) {
                $titleIdx = $i - 1;
                break;
            }
            if ($i > 0 && preg_match('/^-{3,}$/', trim($line)) && trim($lines[$i - 1]) !== '') {
                $titleIdx = $i - 1;
                break;
            }
            // Setext: current line is the title, the next line is the underline
            $next = ($i + 1 < $count) ? trim($lines[$i + 1]) : '';
            if (preg_match('/^={3,}$/', $next) || preg_match('/^-{3,}$/', $next)) {
                $titleIdx = $i;
                break;
            }
            // First non-heading content line: no title to anchor to
            break;
        }
        if ($titleIdx < 0) {
            // No heading: anchor at the very top of the document, so a date
            // field declared on the first line(s) still counts as the doc date.
            $start = 0;
        } else {
            // For setext headings, skip the underline line (=== / ---)
            $start = $titleIdx + 1;
            if ($start < $count && preg_match('/^={3,}$/', trim($lines[$start])) || $start < $count && preg_match('/^-{3,}$/', trim($lines[$start]))) {
                $start++;
            }
        }

        for ($i = $start; $i < $count; $i++) {
            $line = rtrim($lines[$i]);
            if ($line === '') {
                continue;
            }

            // Standalone date line right below the title
            if (preg_match('/^\d{4}-\d{2}-\d{2}\s*$/', $line)) {
                return trim($line);
            }

            // First list block right below the title: scan for a date field item
            if (preg_match('/^[-*+]\s+/', $line)) {
                for (; $i < $count; $i++) {
                    $l = rtrim($lines[$i]);
                    if (preg_match('/^[-*+]\s+(?:date|날짜|일시)\s*[:：]\s*(\d{4}-\d{2}-\d{2})/i', $l, $m)) {
                        return $m[1];
                    }
                    if (!preg_match('/^[-*+]\s+/', $l)) {
                        break;
                    }
                }
                return null;
            }

            // Any other content right below the title → not title-adjacent
            return null;
        }

        return null;
    }

    /**
     * Extract a YYYY-MM-DD date prefix from the filename, or null if absent.
     */
    private static function extractDateFromFilename(string $filePath): ?string
    {
        $filename = PathParser::convertFromOsEncoding(pathinfo($filePath, PATHINFO_FILENAME));
        if (strtotime(substr($filename, 0, 10))) {
            return date('Y-m-d', strtotime(substr($filename, 0, 10)));
        }
        return null;
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

        // First # heading (level-1 only)
        $encoding = mb_detect_encoding($content, ['UTF-8', 'EUC-KR'], true);
        if ($encoding === 'EUC-KR') {
            $content = iconv('EUC-KR', 'UTF-8//IGNORE', $content) ?: $content;
        }

        if (preg_match('/^#[[:blank:]]+(.+)$/m', $content, $m)) {
            return trim($m[1]);
        }
        // Setext heading (underlined with === or ---) — higher priority than ##
        if (preg_match('/^(.+)\n[=]{3,}$/m', $content, $m)) {
            return trim($m[1]);
        }
        // Also catch level-2+ headings as fallback (strip leading #)
        if (preg_match('/^#{2,}[[:blank:]]+(.+)$/m', $content, $m)) {
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
