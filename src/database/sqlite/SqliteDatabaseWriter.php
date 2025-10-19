<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\Envelope;
use spriebsch\DomainEvent\JsonDomainEventSerializer;
use spriebsch\sqlite\Connection;
use SQLite3Stmt;
use Throwable;
use const SQLITE3_NULL;
use const SQLITE3_TEXT;

final class SqliteDatabaseWriter implements DatabaseWriter
{
    private ?SQLite3Stmt $statement = null;

    public static function from(Connection $connection): self
    {
        return new self($connection);
    }

    public function __construct(
        private readonly Connection $connection
    ) {}

    public function beginTransaction(): void
    {
        $this->connection->exec('BEGIN TRANSACTION');
    }

    public function store(Envelope $envelope): void
    {
        try {
            $statement = $this->prepareStatement();

            $eventId = $envelope->eventId()->asString();
            $schemaVersion = $envelope->schemaVersion()->asInt();
            $correlationId = $envelope->correlationId();
            $causationId = $envelope->causationId();
            $receivedAt = $envelope->receivedAt()->asString();
            $persistedAt = null; // Not set at write time
            $topic = $envelope->topic();
            $topicVendor = $topic->vendor();
            $topicDomain = $topic->domain();
            $topicContext = $topic->context();
            $topicName = $topic->name();
            $topicString = $topic->asString();
            $json = (new JsonDomainEventSerializer())->serialize($envelope->payload()->event());

            $statement->bindValue(':eventId', $eventId, SQLITE3_TEXT);
            $statement->bindValue(':schemaVersion', $schemaVersion, SQLITE3_TEXT);

            if ($correlationId === null) {
                $statement->bindValue(':correlationId', null, SQLITE3_NULL);
            } else {
                $statement->bindValue(':correlationId', $correlationId->asUUID()->asString(), SQLITE3_TEXT);
            }

            if ($causationId === null) {
                $statement->bindValue(':causationId', null, SQLITE3_NULL);
            } else {
                $statement->bindValue(':causationId', $causationId->asUUID()->asString(), SQLITE3_TEXT);
            }

            $statement->bindValue(':receivedAt', $receivedAt, SQLITE3_TEXT);
            $statement->bindValue(':persistedAt', $persistedAt, SQLITE3_NULL);
            $statement->bindValue(':topicVendor', $topicVendor, SQLITE3_TEXT);
            $statement->bindValue(':topicDomain', $topicDomain, SQLITE3_TEXT);
            $statement->bindValue(':topicContext', $topicContext, SQLITE3_TEXT);
            $statement->bindValue(':topicName', $topicName, SQLITE3_TEXT);
            $statement->bindValue(':topic', $topicString, SQLITE3_TEXT);
            $statement->bindValue(':event', $json, SQLITE3_TEXT);

            $result = $statement->execute();
            $statement->reset();

            if ($result === false) {
                // Reuse legacy exceptions kept under src/old for now to minimize changes.
                throw new FailedToStoreEventForUnknownReasonException($envelope->eventId());
            }
        } catch (Throwable $exception) {
            throw new FailedToStoreEventException($envelope->eventId(), $exception);
        }
    }

    public function commitTransaction(): void
    {
        $this->connection->exec('COMMIT');
    }

    private function prepareStatement(): SQLite3Stmt
    {
        if ($this->statement === null) {
            $this->statement = $this->connection->prepare(
                'INSERT INTO `sequora-events` (
                    eventId,
                    schemaVersion,
                    correlationId,
                    causationId,
                    receivedAt,
                    persistedAt,
                    topicVendor,
                    topicDomain,
                    topicContext,
                    topicName,
                    topic,
                    event
                ) VALUES (
                    :eventId,
                    :schemaVersion,
                    :correlationId,
                    :causationId,
                    :receivedAt,
                    :persistedAt,
                    :topicVendor,
                    :topicDomain,
                    :topicContext,
                    :topicName,
                    :topic,
                    :event
                );'
            );
        }

        return $this->statement;
    }
}
