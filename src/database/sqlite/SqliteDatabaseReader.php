<?php declare(strict_types=1);

namespace spriebsch\sequora;

use RuntimeException;
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
    public static function from(Connection $connection, array $topicMap): self
    {
        return new self($connection, $topicMap);
    }

    private function __construct(private Connection $connection, private array $topicMap) {}

    public function query(EventQuery $query): Events
    {
        $statement = new EventQuerySqliteSqlBuilder()->build($query, $this->connection);

        $queryResult = $statement->execute();

        $events = [];

        while ($row = $queryResult->fetchArray(SQLITE3_ASSOC)) {

            $topic = Topic::fromString($row['topic']);
            $class = $this->topicMap[$topic->asString()] ?? null;

            if ($class === null) {
                throw new RuntimeException(sprintf('No class found for topic %s', $topic->asString()));
            }

            if ($row['causationId'] !== null) {
                $causationId = CausationId::from(BinaryUUID::from($row['causationId']));
            } else {
                $causationId = null;
            }

            $events[] = Envelope::fromStorage(
                EventId::from(BinaryUUID::from($row['eventId'])),
                Timestamp::from($row['receivedAt']),
                Timestamp::from($row['persistedAt']),
                $row['event'],
                $class,
                $topic,
                $causationId,
                SchemaVersion::from((int) $row['schemaVersion']),
            );
        }

        return Events::from(...$events);
    }
}
