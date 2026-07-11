<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(SequoraWriter::class)]
#[CoversClass(SequoraReader::class)]
#[UsesClass(Events::class)]
#[UsesClass(EventQuery::class)]
#[UsesClass(EventQueryCriteria::class)]
#[UsesClass(EventQuerySqliteSqlBuilder::class)]
#[UsesClass(SqliteDatabaseReader::class)]
#[UsesClass(SqliteDatabaseWriter::class)]
#[UsesClass(SqliteSequoraSchema::class)]
final class SequoraTest extends TestCase
{
    public function test_read_all(): void
    {
        $connection = SqliteConnection::memory();

        $schema = SqliteSequoraSchema::from($connection);
        $schema->createIfNotExists();

        $writer = SequoraWriter::from(SqliteDatabaseWriter::from($connection));

        /** @var array<string, string> $topicMap */
        $topicMap = require __DIR__ . '/doubles/TopicMap.php';
        $reader = SequoraReader::from(SqliteDatabaseReader::from($connection, $topicMap));

        $events = [
            new TestEvent('one'),
            new TestEvent('two'),
            new TestEvent('three'),
        ];

        $writer->store(...$events);

        $result = $reader->all()->asArray();

        $this->assertCount(3, $result);
        $this->assertInstanceOf(TestEvent::class, $result[0]);
        $this->assertSame('one', $result[0]->payload);

        $this->assertInstanceOf(TestEvent::class, $result[1]);
        $this->assertSame('two', $result[1]->payload);

        $this->assertInstanceOf(TestEvent::class, $result[2]);
        $this->assertSame('three', $result[2]->payload);
    }

    public function test_query(): void
    {
        $connection = SqliteConnection::memory();

        $schema = SqliteSequoraSchema::from($connection);
        $schema->createIfNotExists();

        $writer = SequoraWriter::from(SqliteDatabaseWriter::from($connection));

        /** @var array<string, string> $topicMap */
        $topicMap = require __DIR__ . '/doubles/TopicMap.php';
        $reader = SequoraReader::from(SqliteDatabaseReader::from($connection, $topicMap));

        $events = [
            new TestEvent('one'),
            new TestEvent('two'),
            new TestEvent('three'),
        ];

        $writer->store(...$events);

        $query = EventQuery::from();

        $result = $reader->query($query)->asArray();

        $this->assertCount(3, $result);
        $this->assertInstanceOf(TestEvent::class, $result[0]);
        $this->assertSame('one', $result[0]->payload);

        $this->assertInstanceOf(TestEvent::class, $result[1]);
        $this->assertSame('two', $result[1]->payload);

        $this->assertInstanceOf(TestEvent::class, $result[2]);
        $this->assertSame('three', $result[2]->payload);
    }
}
