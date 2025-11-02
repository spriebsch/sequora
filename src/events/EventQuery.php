<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;
use InvalidArgumentException;

final readonly class EventQuery
{
    public static function from(array $conditions = []): self
    {
        return new self($conditions);
    }

    private function __construct(
        private array $conditions
    )
    {
        $this->ensureOnlyAllowedKeys($conditions);
    }

    public function startingAfter(EventId $eventId): self
    {
        return new self(array_merge($this->conditions, ['afterEventId' => $eventId]));
    }

    public function withTopics(Topic ...$topics): self
    {
        if (!is_array($topics)) {
            $topics = [$topics];
        }

        $currentTopics = $this->conditions['topics'] ?? [];

        $topics = array_merge($currentTopics, $topics);

        return new self(array_merge($this->conditions, ['topics' => $topics]));
    }

    public function topics(): array
    {
        return $this->conditions['topics'] ?? [];
    }

    public function afterEventId(): ?EventId
    {
        return $this->conditions['afterEventId'] ?? null;
    }

/*
    public function whereEventId(EventId $eventId): self
    {
        return $this->and("eventId = :eventId", [':eventId' => BinaryUUID::toBinary($eventId)]);
    }

    public function whereCorrelationId(?CorrelationId $correlationId): self
    {
        if ($correlationId === null) {
            return $this->and("correlationId IS NULL", []);
        }

        return $this->and("correlationId = :correlationId", [':correlationId' => BinaryUUID::toBinary($correlationId)]);
    }

    public function whereCausationId(?CausationId $causationId): self
    {
        if ($causationId === null) {
            return $this->and("causationId IS NULL", []);
        }

        return $this->and("causationId = :causationId", [':causationId' => BinaryUUID::toBinary($causationId)]);
    }

    public function whereSchemaVersion(int $schemaVersion): self
    {
        return $this->and("schemaVersion = :schemaVersion", [':schemaVersion' => $schemaVersion]);
    }

    public function whereReceivedAtBetween(Timestamp $from, Timestamp $to): self
    {
        return $this->and("receivedAt BETWEEN :receivedFrom AND :receivedTo", [
            ':receivedFrom' => $from->asString(),
            ':receivedTo'   => $to->asString(),
        ]);
    }

    public function wherePersistedAtBetween(Timestamp $from, Timestamp $to): self
    {
        return $this->and("persistedAt BETWEEN :persistedFrom AND :persistedTo", [
            ':persistedFrom' => $from->asString(),
            ':persistedTo'   => $to->asString(),
        ]);
    }

    public function orderByReceivedAtAsc(): self
    {
        return $this->orderedBy('receivedAt', 'ASC');
    }

    public function orderByReceivedAtDesc(): self
    {
        return $this->orderedBy('receivedAt', 'DESC');
    }

    public function orderByPersistedAtAsc(): self
    {
        return $this->orderedBy('persistedAt', 'ASC');
    }

    public function orderByPersistedAtDesc(): self
    {
        return $this->orderedBy('persistedAt', 'DESC');
    }

    public function limit(int $limit): self
    {
        return new self($this->where, $this->params, $this->orderBy, $this->orderDirection, $limit, $this->offset);
    }

    public function offset(int $offset): self
    {
        return new self($this->where, $this->params, $this->orderBy, $this->orderDirection, $this->limit, $offset);
    }

    public function where(): array
    {
        return $this->where;
    }

    public function params(): array
    {
        return $this->params;
    }

    public function orderBy(): ?string
    {
        return $this->orderBy;
    }

    public function orderDirection(): string
    {
        return $this->orderDirection;
    }

    public function limitValue(): ?int
    {
        return $this->limit;
    }

    public function offsetValue(): ?int
    {
        return $this->offset;
    }

    private function and(string $condition, array $params): self
    {
        $where = $this->where;
        $where[] = $condition;
        $merged = $this->params + $params;

        return new self($where, $merged, $this->orderBy, $this->orderDirection, $this->limit, $this->offset);
    }

    private function orderedBy(string $column, string $direction): self
    {
        return new self($this->where, $this->params, $column, $direction, $this->limit, $this->offset);
    }
    */

    private function ensureOnlyAllowedKeys(array $conditions): void
    {
        $allowedKeys = ['topics', 'afterEventId'];
        $keys = array_keys($conditions);

        $unknown = array_diff($keys, $allowedKeys);

        if ($unknown !== []) {
            $list = implode(', ', $unknown);
            throw new InvalidArgumentException(sprintf('Unknown condition key(s): %s', $list));
        }
    }
}
