<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\sqlite\SqliteConnection;
use SQLite3;
use SQLite3Result;

#[CoversClass(DumpEventStore::class)]
final class DumpEventStoreTest extends TestCase
{
    private string $dbFile;

    protected function setUp(): void
    {
        $this->dbFile = tempnam(sys_get_temp_dir(), 'test_db');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbFile)) {
            unlink($this->dbFile);
        }
    }

    public function test_dump_empty_store(): void
    {
        $connection = SqliteConnection::from($this->dbFile);
        $connection->exec('CREATE TABLE `sequora-events` (id INTEGER PRIMARY KEY)');

        ob_start();
        DumpEventStore::dump($connection);
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('+', $output);
        $this->assertStringContainsString('| id |', $output);
    }

    public function test_dump_with_data(): void
    {
        $connection = SqliteConnection::from($this->dbFile);
        $connection->exec('CREATE TABLE `sequora-events` (id INTEGER PRIMARY KEY, name TEXT)');
        $connection->exec('INSERT INTO `sequora-events` (id, name) VALUES (1, "event-1")');

        ob_start();
        DumpEventStore::dump($connection);
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('| id | name    |', $output);
        $this->assertStringContainsString('| 1  | event-1 |', $output);
    }
}
