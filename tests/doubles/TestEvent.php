<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\JsonDomainEventTrait;
use spriebsch\DomainEvent\MapToTopic;

#[MapToTopic('spriebsch.sequora.test.event')]
final class TestEvent implements DomainEvent
{
    use JsonDomainEventTrait;

    public function __construct(public ?string $payload = null) {}

    public function asArray(): array
    {
        return ['payload' => $this->payload];
    }

    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data) || !array_key_exists('payload', $data) || ($data['payload'] !== null && !is_string($data['payload']))) {
            throw new \InvalidArgumentException('Invalid test event payload');
        }

        return new static($data['payload']);
    }
}
