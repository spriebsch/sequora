<?php declare(strict_types=1);

namespace spriebsch\sequora\tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\sequora\SqliteSequoraSchema;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(SqliteSequoraSchema::class)]
final class SqliteSequoraSchemaTest extends TestCase
{
    public function test_creates_schema(): void
    {
        $connection = SqliteConnection::memory();

        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $result = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='sequora-events'");
        $row = $result->fetchArray(\SQLITE3_ASSOC);

        $this->assertNotFalse($row);
        $this->assertSame('sequora-events', $row['name']);
    }

    public function test_table_has_expected_columns(): void
    {
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $result = $connection->query("PRAGMA table_info('sequora-events')");

        $columns = [];
        while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
            $columns[] = $row['name'];
        }

        $expected = [
            'id',
            'eventId',
            'schemaVersion',
            'correlationId',
            'causationId',
            'receivedAt',
            'persistedAt',
            'topicVendor',
            'topicDomain',
            'topicContext',
            'topicName',
            'topic',
            'event',
        ];

        $this->assertSame($expected, $columns);
    }

    public function test_does_nothing_when_table_exists(): void
    {
        $connection = SqliteConnection::memory();

        $schema = SqliteSequoraSchema::from($connection);
        $schema->createIfNotExists();
        $schema->createIfNotExists();

        $this->expectNotToPerformAssertions();
    }
}
