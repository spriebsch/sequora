<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\MapToTopic;
use spriebsch\DomainEvent\UseAsCorrelationId;

#[MapToTopic('spriebsch.sequora.test.event-with-correlation-id')]
class TestEventWithCorrelationId implements DomainEvent
{
    public function __construct(private TestId $testId, public ?string $payload = null) {}

    #[UseAsCorrelationId]
    public function testId(): TestId
    {
        return $this->testId;
    }
}
