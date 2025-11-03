<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\sqlite\SqliteConnection;
use SQLite3Stmt;

final readonly class EventQuerySqliteSqlBuilder
{
    /**
     * @return array{sql: string, params: array<string, mixed>}
     */
    public function build(EventQuery $query, SqliteConnection $connection): SQLite3Stmt
    {
        $parameters = [];

        $where = [];

        $where = $this->addCorrelationId($query, $where, $connection);
        $where = $this->addCausationId($query, $where, $connection);
        $where = $this->addEventId($query, $where, $connection);
        $where = $this->addTopic($query, $where, $connection);

        $sql = 'SELECT * FROM `sequora-events`';

        if (count($where) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $statement = $connection->prepare($sql);

        return $statement;
    }

    private function addCorrelationId(EventQuery $query, array $where, SqliteConnection $connection): array
    {
        if ($query->correlationIdValue() === null) {
            return $where;
        }

        $where[] = sprintf(
            'correlationId=\'%s\'',
            $connection->escapeString(
                BinaryUUID::toBinary($query->correlationIdValue())
            )
        );

        return $where;
    }

    private function addCausationId(EventQuery $query, array $where, SqliteConnection $connection): array
    {
        if ($query->causationIdValue() === null) {
            return $where;
        }

        $where[] = sprintf(
            'causationId=\'%s\'',
            $connection->escapeString(
                BinaryUUID::toBinary($query->causationIdValue())
            )
        );

        return $where;
    }

    private function addEventId(EventQuery $query, array $where, SqliteConnection $connection): array
    {
        if ($query->afterValue() === null) {
            return $where;
        }

        $where[] = sprintf(
            'id>(SELECT id FROM `sequora-events` WHERE eventId=\'%s\')',
            $connection->escapeString(
                BinaryUUID::toBinary($query->afterValue())
            )
        );

        return $where;
    }

    private function addTopic(EventQuery $query, array $where, SqliteConnection $connection): array
    {
        $topics = [];

        if (count($query->topicsValue()) === 0) {
            return $where;
        }

        foreach ($query->topicsValue() as $topic) {
            $topics[] = '\'' . $connection->escapeString($topic->asString()) . '\'';
        }

        $where[] = 'topic IN (' . implode(',', $topics) . ')';

        return $where;
    }
}
