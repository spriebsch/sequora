<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\Envelope;

class SequoraWriter implements EventWriter
{
    public function __construct(private DatabaseWriter $dbWriter) {}

    public function store(DomainEvent ...$events): void
    {
        $this->dbWriter->beginTransaction();

        foreach ($events as $event) {
            $this->dbWriter->store(Envelope::from($event));
        }

        $this->dbWriter->commitTransaction();
    }
}
