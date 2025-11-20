<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\Envelope;

class SequoraWriter implements EventWriter
{
    private function __construct(private DatabaseWriter $dbWriter) {}

    public static function from(DatabaseWriter $dbWriter): self
    {
        return new self($dbWriter);
    }

    public function store(DomainEvent ...$events): void
    {
        $envelopes = [];

        foreach ($events as $event) {
            $envelopes[] = Envelope::from($event);
        }

        $this->dbWriter->storeEnvelopes(...$envelopes);
    }
}
