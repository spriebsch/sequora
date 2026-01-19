<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\sqlite\Connection;
use SQLite3Stmt;

final readonly class EventQuerySqliteSqlBuilder
{
    public function build(EventQuery $query, Connection $connection): SQLite3Stmt
    {
        $where = [];

        $where = $this->addCorrelationIds($query, $where, $connection);
        $where = $this->addCausationId($query, $where, $connection);
        $where = $this->addEventId($query, $where, $connection);
        $where = $this->addTopic($query, $where, $connection);

        $sql = 'SELECT * FROM `sequora-events`';

        if (count($where) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= $this->orderBySequenceNumber();
        $sql .= $this->limit($query);

        $statement = $connection->prepare($sql);

        return $statement;
    }

    /**
     * @param string[] $where
     * @return string[]
     */
    private function addCorrelationIds(EventQuery $query, array $where, Connection $connection): array
    {
        if (count($query->correlationIdValues()) === 0) {
            return $where;
        }

        $values = [];

        foreach ($query->correlationIdValues() as $correlationId) {
            $values[] = "'" . $connection->escapeString(BinaryUUID::toBinary($correlationId)) . "'";
        }

        $where[] = sprintf(
            'correlationId IN (%s)',
            implode(', ', $values)
        );

        return $where;
    }

    /**
     * @param string[] $where
     * @return string[]
     */
    private function addCausationId(EventQuery $query, array $where, Connection $connection): array
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

    /**
     * @param string[] $where
     * @return string[]
     */
    private function addEventId(EventQuery $query, array $where, Connection $connection): array
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

    /**
     * @param string[] $where
     * @return string[]
     */
    private function addTopic(EventQuery $query, array $where, Connection $connection): array
    {
        $topics = [];

        if (count($query->topicsValue()) === 0) {
            return $where;
        }

        foreach ($query->topicsValue() as $topic) {
            $topics[] = "'" . $connection->escapeString($topic->asString()) . "'";
        }

        $where[] = sprintf('topic IN (%s)', implode(',', $topics));

        return $where;
    }

    private function orderBySequenceNumber(): string
    {
        return ' ORDER BY id ASC';
    }

    private function limit(EventQuery $query): string
    {
        if ($query->limitValue() === null) {
            return '';
        }

        return ' LIMIT ' . $query->limitValue();
    }
}
