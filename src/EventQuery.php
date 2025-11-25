<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\CausationId;
use spriebsch\DomainEvent\CorrelationId;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;
use InvalidArgumentException;
use spriebsch\uuid\UUID;

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

    public function withCorrelationId(UUID $correlationId): self
    {
        if (isset($this->conditions['correlationIds'])) {
            $conditions = $this->conditions;
            $conditions['correlationIds'][] = $correlationId;

            return new self($conditions);
        }

        return new self(array_merge($this->conditions, ['correlationIds' => [$correlationId]]));
    }

    public function withCausationId(CausationId $causationId): self
    {
        return new self(array_merge($this->conditions, ['causationId' => $causationId]));
    }

    public function withTopics(Topic ...$topics): self
    {
        $currentTopics = $this->conditions['topics'] ?? [];

        $topics = array_merge($currentTopics, $topics);

        return new self(array_merge($this->conditions, ['topics' => $topics]));
    }

    public function correlationIdValues(): array
    {
        return $this->conditions['correlationIds'] ?? [];
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
        $allowedKeys = ['topics', 'afterEventId', 'correlationIds', 'causationId', 'limit'];
        $keys = array_keys($conditions);

        $unknown = array_diff($keys, $allowedKeys);

        if ($unknown !== []) {
            $list = implode(', ', $unknown);
            throw new InvalidArgumentException(sprintf('Unknown condition key(s): %s', $list));
        }
    }
}
