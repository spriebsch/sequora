<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\Topic;
use spriebsch\sqlite\SqliteConnection;

#[CoversClass(EventQuerySqliteSqlBuilder::class)]
#[UsesClass(EventQuery::class)]
final class EventQuerySqliteSqlBuilderTest extends TestCase
{
    #[DataProvider('provideQueries')]
    public function test_some(EventQuery $query, string $sql): void
    {
        $connection = SqliteConnection::memory();
        SqliteSequoraSchema::from($connection)->createIfNotExists();

        $statement = new EventQuerySqliteSqlBuilder()->build($query, $connection);

        $this->assertSame($sql, $statement->getSQL());
    }

    public static function provideQueries(): array
    {
        return [
            [
                EventQuery::from(),
                'SELECT * FROM `sequora-events`',
            ],
            [
                EventQuery::from()->withTopics(Topic::fromString('the-vendor.the-domain.the-context.the-name')),
                'SELECT * FROM `sequora-events` WHERE topic IN (\'the-vendor.the-domain.the-context.the-name\')',
            ],
        ];
    }
}
