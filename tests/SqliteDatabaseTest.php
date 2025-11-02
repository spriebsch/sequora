<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\Envelope;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(SqliteDatabaseWriter::class)]
#[CoversClass(SqliteDatabaseReader::class)]
final class SqliteDatabaseTest extends TestCase
{
    public function test_writes_and_reads_events(): void
    {
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $writer = SqliteDatabaseWriter::from($connection);
        $reader = SqliteDatabaseReader::from($connection);

        $event1 = new TestEvent();
        $event2 = new TestEvent();

        $writer->storeEnvelopes(Envelope::from($event1), Envelope::from($event2));

        $events = $reader->query(EventQuery::from());

        $this->assertCount(2, $events);
        $this->assertEquals($event1, $events->asArray()[0]);
        $this->assertEquals($event2, $events->asArray()[1]);
    }
}
