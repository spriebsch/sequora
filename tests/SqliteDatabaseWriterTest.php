<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\Envelope;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(SqliteDatabaseWriter::class)]
final class SqliteDatabaseWriterTest extends TestCase
{
    public function test_something(): void
    {
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $sqliteWriter = new SqliteDatabaseWriter($connection);

        $envelope = Envelope::from(new TestEvent());

        $sqliteWriter->store($envelope);
        var_dump($sqliteWriter);
    }
}
