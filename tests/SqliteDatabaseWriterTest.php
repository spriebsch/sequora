<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use spriebsch\DomainEvent\Envelope;
use spriebsch\sqlite\Connection;

#[CoversClass(SqliteDatabaseWriter::class)]
#[CoversClass(FailedToStoreEventException::class)]
final class SqliteDatabaseWriterTest extends TestCase
{
    public function test_exception_when_write_fails(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('prepare')->willThrowException(new RuntimeException());

        $writer = SqliteDatabaseWriter::from($connection);

        $event = new TestEvent();

        $this->expectException(FailedToStoreEventException::class);

        $writer->storeEnvelopes(Envelope::from($event));
    }
}
