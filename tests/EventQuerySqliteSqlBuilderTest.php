<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(EventQuerySqliteSqlBuilder::class)]
#[UsesClass(EventQuery::class)]
final class EventQuerySqliteSqlBuilderTest extends TestCase
{
    #[DataProvider('provideQueries')]
    public function test_builds_queries(EventQuery $query, string $sql): void
    {
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $statement = new EventQuerySqliteSqlBuilder()->build($query, $connection);

        $this->assertSame($sql, $statement->getSQL());
    }

    public static function provideQueries(): array
    {
        $eventId = EventId::generate();

        return [
            [
                EventQuery::from(),
                'SELECT * FROM `sequora-events`',
            ],
            [
                EventQuery::from()->withTopics(Topic::fromString('the-vendor.the-domain.the-context.the-name')),
                'SELECT * FROM `sequora-events` WHERE topic IN (\'the-vendor.the-domain.the-context.the-name\')',
            ],
            [
                EventQuery::from()->startingAfter($eventId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE id > (SELECT id FROM `sequora-events` WHERE eventId=\'%s\')',
                    BinaryUUID::toBinary($eventId)
                )
            ],
            [
                EventQuery::from()
                    ->withTopics(Topic::fromString('the-vendor.the-domain.the-context.the-name'))
                    ->startingAfter($eventId),
                sprintf(
                    'SELECT * FROM `sequora-events` WHERE id > (SELECT id FROM `sequora-events` WHERE eventId=\'%s\') AND topic IN (\'the-vendor.the-domain.the-context.the-name\')',
                    BinaryUUID::toBinary($eventId)
                )
            ],
        ];
    }
}
