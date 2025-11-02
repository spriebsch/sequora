<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\CausationId;
use spriebsch\DomainEvent\Envelope;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\SchemaVersion;
use spriebsch\DomainEvent\Topic;
use spriebsch\sqlite\Connection;
use spriebsch\timestamp\Timestamp;
use const SQLITE3_ASSOC;

final readonly class SqliteDatabaseReader implements DatabaseReader
{
    public static function from(Connection $connection): self
    {
        return new self($connection);
    }

    private function __construct(private Connection $connection) {}

    public function query(EventQuery $query): Events
    {
        $statement = new EventQuerySqliteSqlBuilder()->build($query, $this->connection);

        $queryResult = $statement->execute();

        $events = [];

        while ($row = $queryResult->fetchArray(SQLITE3_ASSOC)) {
            $events[] = Envelope::fromStorage(
                EventId::from(BinaryUUID::from($row['eventId'])),
                Timestamp::from($row['receivedAt']),
                Timestamp::from($row['persistedAt']),
                $row['event'],
                TestEvent::class,
                Topic::fromString($row['topic']),
                isset($row['causationId']) && $row['causationId'] !== null ? CausationId::from(BinaryUUID::from($row['causationId'])) : null,
                SchemaVersion::from((int) $row['schemaVersion']),
            );
        }

        return Events::from(...$events);
    }
}
