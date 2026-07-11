<?php declare(strict_types=1);

namespace spriebsch\sequora;

use RuntimeException;
use spriebsch\DomainEvent\CausationId;
use spriebsch\DomainEvent\CorrelationId;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;
use InvalidArgumentException;
use spriebsch\uuid\UUID;

final readonly class EventQuery
{
    /**
     * @param array<string, mixed> $conditions
     */
    public static function from(array $conditions = []): self
    {
        return new self($conditions);
    }

    /**
     * @param array<string, mixed> $conditions
     */
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
            /** @var array<UUID> $correlationIds */
            $correlationIds = $conditions['correlationIds'];
            $correlationIds[] = $correlationId;
            $conditions['correlationIds'] = $correlationIds;

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
        /** @var array<Topic> $currentTopics */
        $currentTopics = $this->conditions['topics'] ?? [];

        $topics = array_merge($currentTopics, $topics);

        return new self(array_merge($this->conditions, ['topics' => $this->makeUnique($topics)]));
    }

    public function criteria(): EventQueryCriteria
    {
        /** @var Topic[] $topics */
        $topics = $this->conditions['topics'] ?? [];

        /** @var EventId|null $afterEventId */
        $afterEventId = $this->conditions['afterEventId'] ?? null;

        /** @var UUID[] $correlationIds */
        $correlationIds = $this->conditions['correlationIds'] ?? [];

        /** @var CausationId|null $causationId */
        $causationId = $this->conditions['causationId'] ?? null;

        /** @var int|null $limit */
        $limit = $this->conditions['limit'] ?? null;

        return new EventQueryCriteria(
            $topics,
            $afterEventId,
            $correlationIds,
            $causationId,
            $limit
        );
    }

    /**
     * @param array<string, mixed> $conditions
     */
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

    /**
     * @param Topic[] $topics
     * @return Topic[]
     */
    private function makeUnique(array $topics): array
    {
        $uniqueTopics = [];

        foreach ($topics as $topic) {
            $uniqueTopics[$topic->asString()] = $topic;
        }

        return array_values($uniqueTopics);
    }
}
