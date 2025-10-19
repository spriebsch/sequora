<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\Envelope;

class SequoraWriter
{
    public function store(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->storeEnvelope(Envelope::from($event));
        }
    }

    private function storeEnvelope(Envelope $envelope): void
    {
        $this->dbWriter->write($envelope);
    }
}

// use an db-specific implementation
