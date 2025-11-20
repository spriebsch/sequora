<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(SequoraWriter::class)]
#[CoversClass(SequoraReader::class)]
final class SequoraTest extends TestCase
{
    public function test_one_topic(): void
    {
        $connection = SqliteConnection::memory();

        $schema = SqliteSequoraSchema::from($connection);
        $schema->createIfNotExists();

        $writer = SequoraWriter::from(SqliteDatabaseWriter::from($connection));
        $reader = SequoraReader::from(SqliteDatabaseReader::from($connection));

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
}
