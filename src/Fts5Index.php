<?php

declare(strict_types=1);

/**
 * SQLite FTS5 search index for markdown documents.
 * 
 * Database file: ./search.db (auto-created, .gitignored)
 * 
 * Usage:
 *   $idx = new Fts5Index();
 *   $idx->rebuild();                     // Full re-index
 *   $idx->upsert('/path/to/file.md');    // Single file update
 *   $idx->delete('/path/to/file.md');    // Remove from index
 *   $idx->search('react hooks', '글');   // Search, optionally scoped to root
 */

class Fts5Index
{
    private SQLite3 $db;
    private string $dbPath;

    public function __construct()
    {
        $this->dbPath = ROOT . '/search.db';
        // Isolated database for tests (set by tests/Fts5IndexTest.php)
        if (getenv('FTS5_TEST_DB') === '1') {
            $this->dbPath = ROOT . '/tests/_temp/search-test.db';
        }
        $this->db = new SQLite3($this->dbPath);
        $this->db->enableExceptions(true);
        $this->initTables();
    }

    private function initTables(): void
    {
        // Meta table: stores config hash for staleness detection
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS meta (
                key   TEXT PRIMARY KEY,
                value TEXT
            )
        ");

        // FTS5 virtual table: document full-text index
        $this->db->exec("
            CREATE VIRTUAL TABLE IF NOT EXISTS docs_fts USING fts5(
                path,
                root_name,
                title,
                content,
                tokenize='porter unicode61 remove_diacritics 0'
            )
        ");
    }

    /**
     * Record the current time as the last index update.
     */
    private function touchIndex(): void
    {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO meta (key, value) VALUES ('last_indexed_at', :now)");
        $stmt->bindValue(':now', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * When was the search index last updated (rebuild, upsert or delete)?
     * Returns null when the index has never been built.
     */
    public function getLastIndexedAt(): ?string
    {
        $value = $this->db->querySingle("SELECT value FROM meta WHERE key = 'last_indexed_at'");
        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Check if the index is stale (config.php doc_roots changed).
     */
    public function isStale(): bool
    {
        global $doc_roots;
        $currentHash = md5(serialize($doc_roots) . serialize(array_keys($doc_roots)));
        $storedHash = $this->db->querySingle(
            "SELECT value FROM meta WHERE key = 'doc_roots_hash'"
        );
        return $storedHash !== $currentHash;
    }

    /**
     * Mark the index as up-to-date with current config.php.
     */
    private function markFresh(): void
    {
        global $doc_roots;
        $hash = md5(serialize($doc_roots) . serialize(array_keys($doc_roots)));
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO meta (key, value) VALUES ('doc_roots_hash', :hash)");
        $stmt->bindValue(':hash', $hash, SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * Rebuild the entire index from all doc_roots.
     * @return int Number of files indexed.
     */
    public function rebuild(): int
    {
        global $doc_roots, $markdown_ext_list;

        // Clear existing index
        $this->db->exec("DELETE FROM docs_fts");

        $count = 0;
        $extPattern = '{' . implode(',', $markdown_ext_list) . '}';

        $stmt = $this->db->prepare(
            "INSERT INTO docs_fts (path, root_name, title, content) VALUES (:path, :root, :title, :content)"
        );

        foreach ($doc_roots as $rootName => $rootPath) {
            if (!is_dir($rootPath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file->isFile()) continue;
                if (!in_array(strtolower($file->getExtension()), $markdown_ext_list, true)) continue;

                // Skip common non-content directories
                $relPath = str_replace($rootPath, '', $file->getPathname());
                if (preg_match('#/(\.git|vendor|node_modules|backup|\.Trash)/#', $relPath)) continue;

                $content = @file_get_contents($file->getPathname());
                if ($content === false) continue;

                $encoding = mb_detect_encoding($content, ['UTF-8', 'EUC-KR', 'CP949', 'ASCII'], true);
                if (in_array($encoding, ['EUC-KR', 'CP949'], true)) {
                    $content = iconv((string)$encoding, 'UTF-8//IGNORE', $content) ?: $content;
                }

                $title = FileUtils::extractTitle($file->getPathname());

                $stmt->bindValue(':path', $file->getPathname(), SQLITE3_TEXT);
                $stmt->bindValue(':root', $rootName, SQLITE3_TEXT);
                $stmt->bindValue(':title', $title, SQLITE3_TEXT);
                $stmt->bindValue(':content', $content, SQLITE3_TEXT);
                $stmt->execute();
                $stmt->reset();
                $count++;
            }
        }

        $this->markFresh();
        $this->touchIndex();
        return $count;
    }

    /**
     * Upsert a single file into the index.
     */
    public function upsert(string $filePath): void
    {
        global $doc_roots;

        if (!is_file($filePath)) {
            $this->delete($filePath);
            return;
        }

        // Find which root_name this file belongs to
        $rootName = null;
        foreach ($doc_roots as $name => $path) {
            if (str_starts_with($filePath, realpath($path) ?: $path)) {
                $rootName = $name;
                break;
            }
        }
        if ($rootName === null) return;

        $content = @file_get_contents($filePath);
        if ($content === false) return;

        $encoding = mb_detect_encoding($content, ['UTF-8', 'EUC-KR', 'CP949', 'ASCII'], true);
        if (in_array($encoding, ['EUC-KR', 'CP949'], true)) {
            $content = iconv((string)$encoding, 'UTF-8//IGNORE', $content) ?: $content;
        }

        $title = FileUtils::extractTitle($filePath);

        // Delete old entry if exists, then insert
        $delStmt = $this->db->prepare("DELETE FROM docs_fts WHERE path = :path");
        $delStmt->bindValue(':path', $filePath, SQLITE3_TEXT);
        $delStmt->execute();

        $insStmt = $this->db->prepare(
            "INSERT INTO docs_fts (path, root_name, title, content) VALUES (:path, :root, :title, :content)"
        );
        $insStmt->bindValue(':path', $filePath, SQLITE3_TEXT);
        $insStmt->bindValue(':root', $rootName, SQLITE3_TEXT);
        $insStmt->bindValue(':title', $title, SQLITE3_TEXT);
        $insStmt->bindValue(':content', $content, SQLITE3_TEXT);
        $insStmt->execute();
        $this->touchIndex();
    }

    /**
     * Remove a file from the index.
     */
    public function delete(string $filePath): void
    {
        $stmt = $this->db->prepare("DELETE FROM docs_fts WHERE path = :path");
        $stmt->bindValue(':path', $filePath, SQLITE3_TEXT);
        $stmt->execute();
        $this->touchIndex();
    }

    /**
     * Search the index.
     *
     * @param string $query Search terms (FTS5 query syntax)
     * @param string|null $rootName Optional: scope to a specific doc_root
     * @param int $limit Maximum results
     * @return array<int, array{path: string, root_name: string, title: string, snippet: string}>
     */
    public function search(string $query, ?string $rootName = null, int $limit = 20): array
    {
        // Sanitize query for FTS5
        $ftsQuery = $this->sanitizeQuery($query);

        if ($ftsQuery === '') {
            return [];
        }

        $sql = "SELECT path, root_name, title, "
             . "snippet(docs_fts, 3, '<mark>', '</mark>', '…', 64) as snippet "
             . "FROM docs_fts WHERE docs_fts MATCH :query";

        $params = [':query' => $ftsQuery];

        if ($rootName !== null) {
            $sql .= " AND root_name = :root";
            $params[':root'] = $rootName;
        }

        $sql .= " ORDER BY rank LIMIT :limit";
        $params[':limit'] = $limit;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $type = is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT;
            $stmt->bindValue($key, $value, $type);
        }

        $result = $stmt->execute();
        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Sanitize user input for FTS5 query syntax.
     * Escapes special characters and adds OR between words.
     */
    private function sanitizeQuery(string $query): string
    {
        // Trim and normalize whitespace
        $query = trim(preg_replace('/\s+/', ' ', $query));

        if ($query === '') {
            return '';
        }

        // Remove FTS5 special characters except quotes and wildcards
        $query = preg_replace('/[^a-zA-Z0-9가-힣\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{4E00}-\x{9FFF}\x{AC00}-\x{D7AF}\s"\'\*\-_]/u', ' ', $query);
        $query = trim(preg_replace('/\s+/', ' ', $query));

        if ($query === '' || $query === '""') {
            return '';
        }

        // If single quoted term or single word, use as-is
        $wordCount = count(preg_split('/\s+/', str_replace('"', '', $query), -1, PREG_SPLIT_NO_EMPTY));
        if ($wordCount <= 1 && mb_strlen($query) <= 100) {
            // Remove surrounding quotes if present, we'll add our own
            $term = trim($query, '"\'');
            if ($term === '') return '';
            return '"' . addslashes($term) . '"';
        }

        // Otherwise: OR between tokens
        $terms = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);
        $ftsTerms = array_map(fn($t) => '"' . addslashes(trim($t, '"\'')) . '"', $terms);
        return implode(' OR ', $ftsTerms);
    }
}
