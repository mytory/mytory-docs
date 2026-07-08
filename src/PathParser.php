<?php

declare(strict_types=1);

/**
 * Parse a path string like "view:글/IT/react.md" into its components.
 */

class PathParser
{
    /**
     * @return array{cmd: string, root_name: string, relative_path: string, 
     *               file: string, root_path: string, full_path: string, 
     *               real_full_path: string}
     */
    public static function parse(string $path_string): array
    {
        global $doc_roots;

        // Decode and convert encoding
        $path_string = self::convertToOsEncoding($path_string);

        // Split: "view:글/IT/react.md" → ["view", "글/IT/react.md"]
        $parts = explode(':', $path_string, 2);
        $cmd = $parts[0] ?: '';
        $rest = $parts[1] ?? '';

        $segments = explode('/', $rest);
        $root_name = array_shift($segments) ?: '';

        if (!isset($doc_roots[$root_name])) {
            throw new \RuntimeException("Unknown doc_root: {$root_name}");
        }

        $root_path = realpath($doc_roots[$root_name]) ?: $doc_roots[$root_name];
        $relative_path = implode('/', $segments);
        $real_full_path = $root_path;

        $file = '';
        if ($relative_path !== '') {
            $real_full_path .= '/' . $relative_path;
        }

        // If the path points to a file, split off the filename.
        // Detect file by: (a) it exists on disk, or (b) it has a known extension.
        $isFile = is_file($real_full_path);
        if (!$isFile && preg_match('/\.\w+$/', $relative_path)) {
            $ext = strtolower(pathinfo($relative_path, PATHINFO_EXTENSION));
            global $markdown_ext_list;
            $isFile = in_array($ext, $markdown_ext_list, true);
        }
        if ($isFile) {
            $file = basename($relative_path);
            $relative_path = dirname($relative_path);
            if ($relative_path === '.') {
                $relative_path = '';
            }
        }

        $full_path = $root_name;
        if ($relative_path !== '') {
            $full_path .= '/' . $relative_path;
        }

        return [
            'cmd'            => $cmd,
            'root_name'      => $root_name,
            'root_path'      => $root_path,
            'relative_path'  => $relative_path,
            'file'           => $file,
            'full_path'      => $full_path,
            'real_full_path' => $real_full_path,
            'real_full_file' => $file ? $real_full_path : '',
        ];
    }

    public static function getCmd(string $path_string): string
    {
        return explode(':', $path_string, 2)[0];
    }

    private static function convertToOsEncoding(string $string): string
    {
        if (strtolower(OS_ENCODING) === 'utf-8') {
            return $string;
        }
        $string_encoding = mb_detect_encoding($string, ['UTF-8', 'EUC-KR', 'CP949', 'ASCII'], true);
        if ($string_encoding !== OS_ENCODING) {
            return iconv((string)$string_encoding, OS_ENCODING . '//IGNORE', $string) ?: $string;
        }
        return $string;
    }

    public static function convertFromOsEncoding(string $string): string
    {
        if (strtolower(OS_ENCODING) === 'utf-8') {
            return $string;
        }
        return iconv(OS_ENCODING, 'utf-8//IGNORE', $string) ?: $string;
    }
}
