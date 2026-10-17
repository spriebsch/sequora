<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\JsonDomainEventTrait;
use spriebsch\DomainEvent\MapToTopic;
use spriebsch\DomainEvent\UseAsCorrelationId;

#[MapToTopic('spriebsch.sequora.test.event-with-correlation-id')]
final class TestEventWithCorrelationId implements DomainEvent
{
    use JsonDomainEventTrait;

    public function __construct(private TestId $testId, public ?string $payload = null) {}

    public function asArray(): array
    {
        return ['testId' => $this->testId->asString(), 'payload' => $this->payload];
    }

    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data) || !is_string($data['testId'] ?? null) || !array_key_exists('payload', $data) || ($data['payload'] !== null && !is_string($data['payload']))) {
            throw new \InvalidArgumentException('Invalid test event payload');
        }

        return new static(TestId::from($data['testId']), $data['payload']);
    }

    #[UseAsCorrelationId]
    public function testId(): TestId
    {
        return $this->testId;
    }
}
