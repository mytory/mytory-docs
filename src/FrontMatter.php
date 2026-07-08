<?php

declare(strict_types=1);

/**
 * Parse YAML front matter from markdown files using regex.
 * Supports simple key: value pairs. No external YAML library needed.
 */

class FrontMatter
{
    /**
     * Extract front matter from markdown content.
     * Removes the front matter block from $content (by reference).
     * 
     * @return array<string, mixed>
     */
    public static function parse(string &$content): array
    {
        if (!preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            return [];
        }

        $content = str_replace($matches[0], '', $content);
        $metadata = [];
        $currentKey = null;

        foreach (explode("\n", trim($matches[1])) as $line) {
            // Nested list item (e.g. "  - foo")
            if ($currentKey !== null && preg_match('/^\s+-\s*(.+)$/', $line, $m)) {
                if (!is_array($metadata[$currentKey])) {
                    $metadata[$currentKey] = [];
                }
                $metadata[$currentKey][] = trim($m[1]);
                continue;
            }

            // Simple key: value
            if (preg_match('/^(\w[\w-]*)\s*:\s*(.*)$/', $line, $m)) {
                $currentKey = $m[1];
                $value = trim($m[2]);

                // Detect arrays: [item1, item2]
                if (preg_match('/^\[(.+)\]$/', $value, $arr)) {
                    $metadata[$currentKey] = array_map('trim', explode(',', $arr[1]));
                } else {
                    $metadata[$currentKey] = $value;
                }
                continue;
            }

            // Key without value (list start marker)
            if (preg_match('/^(\w[\w-]*)\s*:$/', $line, $m)) {
                $currentKey = $m[1];
                $metadata[$currentKey] = [];
                continue;
            }

            $currentKey = null;
        }

        return $metadata;
    }
}
