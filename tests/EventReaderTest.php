<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\Topic;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(SequoraReader::class)]
#[UsesClass(SequoraWriter::class)]
#[UsesClass(Events::class)]
#[UsesClass(BinaryUUID::class)]
#[UsesClass(EventQuery::class)]
#[UsesClass(EventQuerySqliteSqlBuilder::class)]
#[UsesClass(SqliteDatabaseReader::class)]
#[UsesClass(SqliteDatabaseWriter::class)]
#[UsesClass(SqliteSequoraSchema::class)]
final class EventReaderTest extends TestCase
{
    /**
     * @param callable(array<\spriebsch\DomainEvent\Envelope>): EventQuery $thing
     */
    #[DataProvider('provideQueries')]
    public function test_queries(callable $thing, int $numberOfEvents): void
    {
        $connection = SqliteConnection::memory();

        $schema = SqliteSequoraSchema::from($connection);
        $schema->createIfNotExists();

        $writer = SequoraWriter::from(SqliteDatabaseWriter::from($connection));

        /** @var array<string, string> $topicMap */
        $topicMap = require __DIR__ . '/doubles/TopicMap.php';
        $reader = SequoraReader::from(SqliteDatabaseReader::from($connection, $topicMap));

        $events = [
            new TestEventWithCorrelationId(TestId::generate(), 'one'),
            new TestEventWithCorrelationId(TestId::generate(), 'two'),
            new TestEventWithCorrelationId(TestId::generate(), 'three'),
        ];

        $writer->store(...$events);

        $envelopes = $reader->all()->envelopes();

        $query = $thing($envelopes);

        $result = $reader->query($query)->asArray();

        $this->assertCount($numberOfEvents, $result);
    }

    /**
     * @return array<string, array{0: callable(array<\spriebsch\DomainEvent\Envelope>): EventQuery, 1: int}>
     */
    public static function provideQueries(): array
    {
        return [
            'all' => [
                fn(array $envelopes) => EventQuery::from(),
                3
            ],

            /*
            'topic' => [
                fn(array $envelopes) => EventQuery::from()
                                                  ->withTopics(
                                                      Topic::fromString(
                                                          'spriebsch.sequora.test.event-with-correlation-id'
                                                      )
                                                  ),
                1
            ],
            */

            /*
            'correlation ID' => [
                fn(array $envelopes) => EventQuery::from()
                                                  ->withCorrelationId($envelopes[0]->correlationId()),
                1
            ],
            */
        ];
    }
}
