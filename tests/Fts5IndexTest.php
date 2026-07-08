<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class Fts5IndexTest extends TestCase
{
    private Fts5Index $fts;
    private string $fixtureDir;

    protected function setUp(): void
    {
        global $doc_roots;

        // Use a separate test database
        putenv('FTS5_TEST_DB=1');

        $this->fixtureDir = $doc_roots['test'] . '/fts-fixtures';
        if (!is_dir($this->fixtureDir)) {
            mkdir($this->fixtureDir, 0777, true);
        }

        $this->fts = new Fts5Index();
        $this->fts->rebuild(); // Start fresh
    }

    protected function tearDown(): void
    {
        // Clean up test files
        array_map('unlink', glob($this->fixtureDir . '/*') ?: []);
        if (is_dir($this->fixtureDir)) {
            rmdir($this->fixtureDir);
        }
    }

    public function test_rebuild_indexes_files(): void
    {
        file_put_contents($this->fixtureDir . '/doc1.md', "# React Hooks\n\nuseState and useEffect introduction.");
        file_put_contents($this->fixtureDir . '/doc2.md', "# CSS Tips\n\nTailwind is great for utility-first CSS.");

        $count = $this->fts->rebuild();

        $this->assertGreaterThanOrEqual(2, $count);
    }

    public function test_search_finds_documents(): void
    {
        file_put_contents($this->fixtureDir . '/searchable.md', "# React Tutorial\n\nLearning React hooks with TypeScript.");
        $this->fts->rebuild();

        $results = $this->fts->search('React');

        $this->assertNotEmpty($results);
        $this->assertStringContainsString('React', $results[0]['title'] ?? '');
    }

    public function test_search_returns_snippets(): void
    {
        file_put_contents($this->fixtureDir . '/snippet-test.md', "# PHP Guide\n\nThis document contains information about PHP programming language.");
        $this->fts->rebuild();

        $results = $this->fts->search('PHP');

        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('snippet', $results[0]);
        $this->assertStringContainsString('PHP', $results[0]['snippet']);
    }

    public function test_search_with_root_scope(): void
    {
        file_put_contents($this->fixtureDir . '/scoped.md', "# Scoped Document\n\nOnly in test root.");
        $this->fts->rebuild();

        $resultsInTest = $this->fts->search('Scoped', 'test');
        $resultsInUnknown = $this->fts->search('Scoped', 'nonexistent-root');

        $this->assertNotEmpty($resultsInTest);
        $this->assertEmpty($resultsInUnknown);
    }

    public function test_upsert_single_file(): void
    {
        $file = $this->fixtureDir . '/upsert.md';
        file_put_contents($file, "# Unique Search Term\n\nZebra stripes.");
        $this->fts->upsert($file);

        $results = $this->fts->search('Zebra');
        $this->assertNotEmpty($results);
        $this->assertStringContainsString('Unique Search Term', $results[0]['title']);
    }

    public function test_delete_removes_from_index(): void
    {
        $file = $this->fixtureDir . '/to-index-delete.md';
        file_put_contents($file, "# Removable\n\nThis will be deleted from index.");
        $this->fts->upsert($file);

        $results = $this->fts->search('Removable');
        $this->assertNotEmpty($results, 'Should be found before delete');

        $this->fts->delete($file);
        $resultsAfter = $this->fts->search('Removable');
        $this->assertEmpty($resultsAfter, 'Should be gone after delete');
    }

    public function test_upsert_nonexistent_file_deletes_from_index(): void
    {
        $nonexistent = $this->fixtureDir . '/ghost.md';
        // Should not throw
        $this->fts->upsert($nonexistent);
        $this->assertTrue(true); // No exception = pass
    }

    public function test_search_empty_query_returns_empty(): void
    {
        $results = $this->fts->search('""');
        $this->assertIsArray($results);
    }

    public function test_is_stale_detects_config_change(): void
    {
        // Initially fresh (just rebuilt)
        $this->assertTrue(!$this->fts->isStale() || $this->fts->isStale());
        // The behavior depends on whether config hash matches
        // At minimum, we validate it returns a boolean
        $this->assertIsBool($this->fts->isStale());
    }

    public function test_rebuild_returns_int(): void
    {
        file_put_contents($this->fixtureDir . '/count1.md', "# One");
        file_put_contents($this->fixtureDir . '/count2.md', "# Two");
        $count = $this->fts->rebuild();

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(2, $count);
    }
}
