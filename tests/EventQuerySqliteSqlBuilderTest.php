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
final class EventQuerySqliteSqlBuilderTest extends TestCase
{
    #[DataProvider('provideQueries')]
    public function test_builds_queries(SqliteConnection $connection, EventQuery $query, string $sql): void
    {
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $statement = new EventQuerySqliteSqlBuilder()->build($query, $connection);

        $this->assertSame($sql, $statement->getSQL());
    }

    /*
    public function test_some(): void
    {
        $uuid = UUIDv4::from('51523a51-1441-409b-8181-e444fe651127');
        var_dump($uuid);
        var_dump(BinaryUUID::toBinary($uuid));
    }
    */

    public static function provideQueries(): array
    {
        $connection = SqliteConnection::memory();
        $eventId = EventId::from('51523a51-1441-409b-8181-e444fe651127'); // binary string contains '
        $correlationId = CorrelationId::generate();
        $causationId = CausationId::generate();

        return [
            'all'                 => [
                $connection,
                EventQuery::from(),
                'SELECT * FROM `sequora-events`',
            ],
            'with correlation ID' => [
                $connection,
                EventQuery::from()
                          ->withCorrelationId($correlationId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE correlationId=\'%s\'',
                    $connection->escapeString(BinaryUUID::toBinary($correlationId))
                )
            ],
            'with causation ID' => [
                $connection,
                EventQuery::from()
                          ->withCausationId($causationId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE causationId=\'%s\'',
                    $connection->escapeString(BinaryUUID::toBinary($causationId))
                )
            ],
            [
                $connection,
                EventQuery::from()
                          ->withTopics(Topic::fromString('the-vendor.the-domain.the-context.the-name')),
                'SELECT * FROM `sequora-events` WHERE topic IN (\'the-vendor.the-domain.the-context.the-name\')',
            ],
            [
                $connection,
                EventQuery::from()->after($eventId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE id>(%s)',
                    sprintf(
                        'SELECT id FROM `sequora-events` WHERE eventId=\'%s\'',
                        $connection->escapeString(BinaryUUID::toBinary($eventId))
                    ),
                )
            ],
            [
                $connection,
                EventQuery::from()
                          ->withTopics(Topic::fromString('the-vendor.the-domain.the-context.the-name'))
                          ->after($eventId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE id>(%s) AND topic IN (\'%s\')',
                    sprintf(
                        'SELECT id FROM `sequora-events` WHERE eventId=\'%s\'',
                        $connection->escapeString(BinaryUUID::toBinary($eventId))
                    ),
                    'the-vendor.the-domain.the-context.the-name'
                )
            ],
        ];
    }
}
