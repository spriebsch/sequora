<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\CausationId;
use spriebsch\DomainEvent\CorrelationId;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(EventQuerySqliteSqlBuilder::class)]
#[UsesClass(EventQuery::class)]
#[UsesClass(BinaryUUID::class)]
#[UsesClass(SqliteSequoraSchema::class)]
final class EventQuerySqliteSqlBuilderTest extends TestCase
{
    #[DataProvider('provideQueries')]
    public function test_builds_queries(SqliteConnection $connection, EventQuery $query, string $sql): void
    {
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $statement = new EventQuerySqliteSqlBuilder()->build($query, $connection);

        $this->assertSame($sql, $statement->getSQL());
    }
    
    /**
     * @return array<string, array{0: SqliteConnection, 1: EventQuery, 2: string}>
     */
    public static function provideQueries(): array
    {
        $connection = SqliteConnection::memory();
        $eventId = EventId::from('51523a51-1441-409b-8181-e444fe651127'); // binary string contains '
        $correlationId = CorrelationId::generate();
        $correlationId2 = CorrelationId::generate();
        $causationId = CausationId::generate();
        $topic1 = Topic::fromString('the-vendor.the-domain.the-context.the-name-1');
        $topic2 = Topic::fromString('the-vendor.the-domain.the-context.the-name-2');

        return [
            'all'                       => [
                $connection,
                EventQuery::from(),
                'SELECT * FROM `sequora-events` ORDER BY id ASC',
            ],
            'all with limit'            => [
                $connection,
                EventQuery::from()
                          ->limit(100),
                'SELECT * FROM `sequora-events` ORDER BY id ASC LIMIT 100',
            ],
            'with one correlation ID'   => [
                $connection,
                EventQuery::from()
                          ->withCorrelationId($correlationId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE correlationId=\'%s\' ORDER BY id ASC',
                    $connection->escapeString(BinaryUUID::toBinary($correlationId))
                )
            ],
            'with two correlation IDs'  => [
                $connection,
                EventQuery::from()
                          ->withCorrelationId($correlationId)
                          ->withCorrelationId($correlationId2),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE correlationId IN (\'%s\', \'%s\') ORDER BY id ASC',
                    $connection->escapeString(BinaryUUID::toBinary($correlationId)),
                    $connection->escapeString(BinaryUUID::toBinary($correlationId2))
                )
            ],
            'with causation ID'         => [
                $connection,
                EventQuery::from()
                          ->withCausationId($causationId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE causationId=\'%s\' ORDER BY id ASC',
                    $connection->escapeString(BinaryUUID::toBinary($causationId))
                )
            ],
            'with topic'                => [
                $connection,
                EventQuery::from()
                          ->withTopics($topic1),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE topic IN (\'%s\') ORDER BY id ASC',
                    $topic1->asString()
                )
            ],
            'with topics'               => [
                $connection,
                EventQuery::from()
                          ->withTopics($topic1, $topic2),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE topic IN (\'%s\',\'%s\') ORDER BY id ASC',
                    $topic1->asString(),
                    $topic2->asString()
                )
            ],
            'after event ID'            => [
                $connection,
                EventQuery::from()->after($eventId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE id>(%s) ORDER BY id ASC',
                    sprintf(
                        'SELECT id FROM `sequora-events` WHERE eventId=\'%s\'',
                        $connection->escapeString(BinaryUUID::toBinary($eventId))
                    ),
                )
            ],
            'with topic after event ID' => [
                $connection,
                EventQuery::from()
                          ->withTopics($topic1)
                          ->after($eventId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE id>(%s) AND topic IN (\'%s\') ORDER BY id ASC',
                    sprintf(
                        'SELECT id FROM `sequora-events` WHERE eventId=\'%s\'',
                        $connection->escapeString(BinaryUUID::toBinary($eventId))
                    ),
                    $topic1->asString()
                )
            ],
        ];
    }
}
