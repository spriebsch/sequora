<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\CausationId;
use spriebsch\DomainEvent\CorrelationId;
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

    public function limit(int $limit): self
    {
        return new self(array_merge($this->conditions, ['limit' => $limit]));
    }

    public function after(EventId $eventId): self
    {
        return new self(array_merge($this->conditions, ['afterEventId' => $eventId]));
    }

    public function withCorrelationId(CorrelationId $correlationId): self
    {
        return new self(array_merge($this->conditions, ['correlationId' => $correlationId]));
    }

    public function withCausationId(CausationId $causationId): self
    {
        return new self(array_merge($this->conditions, ['causationId' => $causationId]));
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

    public function correlationIdValue(): ?CorrelationId
    {
        return $this->conditions['correlationId'] ?? null;
    }

    public function causationIdValue(): ?CausationId
    {
        return $this->conditions['causationId'] ?? null;
    }

    public function topicsValue(): array
    {
        return $this->conditions['topics'] ?? [];
    }

    public function afterValue(): ?EventId
    {
        return $this->conditions['afterEventId'] ?? null;
    }

    public function limitValue(): ?int
    {
        return $this->conditions['limit'] ?? null;
    }

    private function ensureOnlyAllowedKeys(array $conditions): void
    {
        $allowedKeys = ['topics', 'afterEventId', 'correlationId', 'causationId', 'limit'];
        $keys = array_keys($conditions);

        $unknown = array_diff($keys, $allowedKeys);

        if ($unknown !== []) {
            $list = implode(', ', $unknown);
            throw new InvalidArgumentException(sprintf('Unknown condition key(s): %s', $list));
        }
    }
}
