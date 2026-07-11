<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\CausationId;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;
use spriebsch\uuid\UUID;

/**
 * @internal
 */
final readonly class EventQueryCriteria
{
    /**
     * @param Topic[] $topics
     * @param UUID[] $correlationIds
     */
    public function __construct(
        private array $topics,
        private ?EventId $afterEventId,
        private array $correlationIds,
        private ?CausationId $causationId,
        private ?int $limit
    ) {}

    /**
     * @return Topic[]
     */
    public function topics(): array
    {
        return $this->topics;
    }

    public function afterEventId(): ?EventId
    {
        return $this->afterEventId;
    }

    /**
     * @return UUID[]
     */
    public function correlationIds(): array
    {
        return $this->correlationIds;
    }

    public function causationId(): ?CausationId
    {
        return $this->causationId;
    }

    public function limit(): ?int
    {
        return $this->limit;
    }
}
