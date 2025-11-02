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

        $where = $this->addTopic($query, $where, $connection);

        $sql = 'SELECT * FROM `sequora-events`';

        if (count($where) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $statement = $connection->prepare($sql);

        return $statement;
    }

    private function addTopic(EventQuery $query, array $where, SqliteConnection $connection): array
    {
        $topics = [];

        if (count($query->topics()) === 0) {
            return $where;
        }

        foreach ($query->topics() as $topic) {
            $topics[] = '\'' . str_replace('\'', '\'\'', $topic->asString()) . '\'';
            // $topic[] = '\'' . $connection->escapeString($topic->asString()) . '\'';
        }

        $where[] = 'topic IN (' . implode(',', $topics) . ')';

        return $where;
    }
}
