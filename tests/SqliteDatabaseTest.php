<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use spriebsch\DomainEvent\CausationId;
use spriebsch\DomainEvent\Envelope;
use spriebsch\DomainEvent\Topic;
use spriebsch\sqlite\Connection;
use spriebsch\sqlite\SqliteConnection;
use SQLite3Stmt;

#[CoversClass(SqliteDatabaseWriter::class)]
#[CoversClass(SqliteDatabaseReader::class)]
#[UsesClass(EventQuery::class)]
#[UsesClass(EventQuerySqliteSqlBuilder::class)]
#[UsesClass(Events::class)]
#[UsesClass(SqliteSequoraSchema::class)]
final class SqliteDatabaseTest extends TestCase
{
    public function test_writes_and_reads_events(): void
    {
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $writer = SqliteDatabaseWriter::from($connection);

        /** @var array<string, string> $topicMap */
        $topicMap = require __DIR__ . '/doubles/TopicMap.php';
        $reader = SqliteDatabaseReader::from($connection, $topicMap);

        $event1 = new TestEvent();
        $event2 = new TestEvent();

        $writer->storeEnvelopes(Envelope::from($event1), Envelope::from($event2));

        $events = $reader->query(EventQuery::from());

        $this->assertCount(2, $events);
        $this->assertEquals($event1, $events->asArray()[0]);
        $this->assertEquals($event2, $events->asArray()[1]);
    }

    public function test_with_causationId(): void
    {
        $causationId = CausationId::generate();
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $writer = SqliteDatabaseWriter::from($connection);

        /** @var array<string, string> $topicMap */
        $topicMap = require __DIR__ . '/doubles/TopicMap.php';
        $reader = SqliteDatabaseReader::from($connection, $topicMap);

        $event = new TestEvent();

        $writer->storeEnvelopes(Envelope::from($event, $causationId));

        $events = $reader->query(EventQuery::from());

        $this->assertCount(1, $events);
        $this->assertEquals($event, $events->asArray()[0]);

        $causationIdValue = $events->envelopes()[0]->causationId();
        $this->assertNotNull($causationIdValue);
        $this->assertTrue($causationIdValue->equals($causationId));
    }

    public function test_with_topic(): void
    {
        $causationId = CausationId::generate();
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $writer = SqliteDatabaseWriter::from($connection);

        /** @var array<string, string> $topicMap */
        $topicMap = require __DIR__ . '/doubles/TopicMap.php';
        $reader = SqliteDatabaseReader::from($connection, $topicMap);

        $event = new TestEvent();

        $writer->storeEnvelopes(Envelope::from($event, $causationId));
        $writer->storeEnvelopes(Envelope::from(new TestEventWithCorrelationId(TestId::generate())));

        $events = $reader->query(
            EventQuery::from()->withTopics(Topic::fromString('spriebsch.sequora.test.event'))
        );

        $this->assertCount(1, $events);
        $this->assertEquals($event, $events->asArray()[0]);

        $causationIdValue = $events->envelopes()[0]->causationId();
        $this->assertNotNull($causationIdValue);
        $this->assertTrue($causationIdValue->equals($causationId));
    }


    public function test_with_correlationId(): void
    {
        $correlationId = TestId::generate();
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $writer = SqliteDatabaseWriter::from($connection);

        /** @var array<string, string> $topicMap */
        $topicMap = require __DIR__ . '/doubles/TopicMap.php';
        $reader = SqliteDatabaseReader::from($connection, $topicMap);

        $event = new TestEventWithCorrelationId($correlationId);

        $writer->storeEnvelopes(Envelope::from(new TestEvent()));
        $writer->storeEnvelopes(Envelope::from($event));

        $events = $reader->query(EventQuery::from()->withCorrelationId($correlationId));

        $this->assertCount(1, $events);
        $this->assertEquals($event, $events->asArray()[0]);

        $correlationIdValue = $events->envelopes()[0]->correlationId();
        $this->assertNotNull($correlationIdValue);
        $this->assertTrue($correlationIdValue->equals($correlationId));
    }

    public function test_exception_on_undefined_event_class(): void
    {
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $writer = SqliteDatabaseWriter::from($connection);
        $reader = SqliteDatabaseReader::from($connection, []);

        $event = new TestEvent();

        $writer->storeEnvelopes(Envelope::from($event));

        $this->expectException(RuntimeException::class);

        $reader->query(EventQuery::from());
    }

    public function test_throws_exception_when_query_execution_fails(): void
    {
        $connection = $this->createStub(Connection::class);
        $statement = $this->createStub(SQLite3Stmt::class);

        $connection->method('prepare')->willReturn($statement);
        $statement->method('execute')->willReturn(false);

        $reader = SqliteDatabaseReader::from($connection, []);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to execute SQLite query');

        $reader->query(EventQuery::from());
    }
}
