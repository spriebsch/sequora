<?php declare(strict_types=1);

namespace spriebsch\sequora;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use spriebsch\DomainEvent\Envelope;
use spriebsch\DomainEvent\EventId;

class Events implements IteratorAggregate, Countable
{
    /**
     * @var Envelope[]
     */
    private array $envelopes;

    public static function from(Envelope ...$envelopes): self
    {
        return new self(...$envelopes);
    }

    private function __construct(Envelope ...$envelopes)
    {
        $this->envelopes = $envelopes;
    }

    public function count(): int
    {
        return count($this->envelopes);
    }

    public function lastEventId(): EventId
    {
        $key = array_key_last($this->envelopes);

        if ($key === null) {
            throw new NoEventsException();
        }

        return $this->envelopes[$key]->eventId();
    }

    public function envelopes(): array
    {
        return $this->envelopes;
    }

    public function asArray(): array
    {
        return array_map(
            fn (Envelope $envelope) => $envelope->payload()->event(),
            $this->envelopes
        );
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->asArray());
    }
}
