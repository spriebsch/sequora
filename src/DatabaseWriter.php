<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\Envelope;

interface DatabaseWriter
{
    public function write(Envelope $envelope): void;
}
