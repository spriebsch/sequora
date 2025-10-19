<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\sqlite\Connection;
use spriebsch\sqlite\SqliteSchema;

final class SqliteSequoraSchema extends SqliteSchema
{
    protected function schemaExists(Connection $connection): bool
    {
        $result = $connection->query(
            "SELECT sql FROM sqlite_master WHERE name='sequora-events';"
        );

        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row === false) {
            return false;
        }

        return $row['sql'] !== $this->sql();
    }

    protected function createSchema(Connection $connection): void
    {
        $connection->exec($this->sql());
        $connection->exec('PRAGMA journal_mode=WAL');
    }

    private function sql(): string
    {
        return 'BEGIN TRANSACTION; CREATE TABLE `sequora-events` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `eventId` TEXT UNIQUE,
            `correlationId` TEXT,
            `topic` TEXT,
            `event` TEXT
        ); END TRANSACTION;';
    }
}
