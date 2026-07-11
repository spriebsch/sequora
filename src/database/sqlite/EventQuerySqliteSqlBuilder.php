<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\sqlite\Connection;
use SQLite3Stmt;

final readonly class EventQuerySqliteSqlBuilder
{
    public function build(EventQuery $query, Connection $connection): SQLite3Stmt
    {
        $criteria = $query->criteria();
        $where = [];

        $where = $this->addCorrelationIds($criteria, $where, $connection);
        $where = $this->addCausationId($criteria, $where, $connection);
        $where = $this->addEventId($criteria, $where, $connection);
        $where = $this->addTopic($criteria, $where, $connection);

        $sql = 'SELECT * FROM `sequora-events`';

        if (count($where) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= $this->orderBySequenceNumber();
        $sql .= $this->limit($criteria);

        $statement = $connection->prepare($sql);

        return $statement;
    }

    /**
     * @param string[] $where
     * @return string[]
     */
    private function addCorrelationIds(EventQueryCriteria $criteria, array $where, Connection $connection): array
    {
        if (count($criteria->correlationIds()) === 0) {
            return $where;
        }

        if (count($criteria->correlationIds()) === 1) {
            $where[] = sprintf(
                "correlationId='%s'",
                $connection->escapeString($criteria->correlationIds()[0]->asString())
            );

            return $where;
        }

        $values = [];

        foreach ($criteria->correlationIds() as $correlationId) {
            $values[] = "'" . $connection->escapeString($correlationId->asString()) . "'";
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
    private function addCausationId(EventQueryCriteria $criteria, array $where, Connection $connection): array
    {
        if ($criteria->causationId() === null) {
            return $where;
        }

        $where[] = sprintf(
            'causationId=\'%s\'',
            $connection->escapeString($criteria->causationId()->asString())
        );

        return $where;
    }

    /**
     * @param string[] $where
     * @return string[]
     */
    private function addEventId(EventQueryCriteria $criteria, array $where, Connection $connection): array
    {
        if ($criteria->afterEventId() === null) {
            return $where;
        }

        $where[] = sprintf(
            'id>(SELECT id FROM `sequora-events` WHERE eventId=\'%s\')',
            $connection->escapeString($criteria->afterEventId()->asString())
        );

        return $where;
    }

    /**
     * @param string[] $where
     * @return string[]
     */
    private function addTopic(EventQueryCriteria $criteria, array $where, Connection $connection): array
    {
        $topics = [];

        if (count($criteria->topics()) === 0) {
            return $where;
        }

        if (count($criteria->topics()) === 1) {
            $where[] = sprintf("topic='%s'", $criteria->topics()[0]->asString());

            return $where;
        }

        foreach ($criteria->topics() as $topic) {
            $topics[] = "'" . $connection->escapeString($topic->asString()) . "'";
        }

        $where[] = sprintf('topic IN (%s)', implode(',', $topics));

        return $where;
    }

    private function orderBySequenceNumber(): string
    {
        return ' ORDER BY id ASC';
    }

    private function limit(EventQueryCriteria $criteria): string
    {
        if ($criteria->limit() === null) {
            return '';
        }

        return ' LIMIT ' . $criteria->limit();
    }
}
