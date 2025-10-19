<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\Envelope;
use spriebsch\DomainEvent\JsonDomainEventSerializer;
use spriebsch\sqlite\Connection;
use spriebsch\timestamp\Timestamp;
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

    private function __construct(
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

            $statement->bindValue(':eventId', $envelope->eventId()->asString(), SQLITE3_TEXT);
            $statement->bindValue(':schemaVersion', $envelope->schemaVersion()->asInt(), SQLITE3_INTEGER);

            if ($envelope->correlationId() === null) {
                $statement->bindValue(':correlationId', null, SQLITE3_NULL);
            } else {
                $statement->bindValue(':correlationId', $envelope->correlationId()->asString(), SQLITE3_TEXT);
            }

            if ($envelope->causationId() === null) {
                $statement->bindValue(':causationId', null, SQLITE3_NULL);
            } else {
                $statement->bindValue(':causationId', $envelope->causationId()->asString(), SQLITE3_TEXT);
            }

            $statement->bindValue(':receivedAt', $envelope->receivedAt()->asString(), SQLITE3_TEXT);
            $statement->bindValue(':persistedAt', Timestamp::generate()->asString(), SQLITE3_TEXT);
            $statement->bindValue(':topicVendor', $envelope->topic()->vendor(), SQLITE3_TEXT);
            $statement->bindValue(':topicDomain', $envelope->topic()->domain(), SQLITE3_TEXT);
            $statement->bindValue(':topicContext', $envelope->topic()->context(), SQLITE3_TEXT);
            $statement->bindValue(':topicName', $envelope->topic()->name(), SQLITE3_TEXT);
            $statement->bindValue(':topic', $envelope->topic()->asString(), SQLITE3_TEXT);
            $statement->bindValue(':event', $envelope->payload()->asJson(), SQLITE3_TEXT);

            $result = $statement->execute();
            $statement->reset();

            if ($result === false) {
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
