<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(DumpEventStore::class)]
final class DumpEventStoreTest extends TestCase
{
    private string $dbFile;

    protected function setUp(): void
    {
        $this->dbFile = tempnam(sys_get_temp_dir(), 'sequora-test-');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbFile)) {
            unlink($this->dbFile);
        }
    }

    public function test_dump_outputs_table(): void
    {
        $connection = SqliteConnection::from($this->dbFile);
        $connection->exec('CREATE TABLE `sequora-events` (id INTEGER PRIMARY KEY, event_type TEXT, payload TEXT)');
        $connection->exec("INSERT INTO `sequora-events` (event_type, payload) VALUES ('TestEvent', '{\"foo\":\"bar\"}')");

        ob_start();
        DumpEventStore::dump($connection);
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('id', $output);
        $this->assertStringContainsString('event_type', $output);
        $this->assertStringContainsString('payload', $output);
        $this->assertStringContainsString('TestEvent', $output);
        $this->assertStringContainsString('{"foo":"bar"}', $output);
    }

    public function test_dump_with_no_data(): void
    {
        $connection = SqliteConnection::from($this->dbFile);
        $connection->exec('CREATE TABLE `sequora-events` (id INTEGER PRIMARY KEY, event_type TEXT, payload TEXT)');

        ob_start();
        DumpEventStore::dump($connection);
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('id', $output);
        $this->assertStringContainsString('event_type', $output);
        $this->assertStringContainsString('payload', $output);
    }
}
