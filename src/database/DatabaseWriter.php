<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\Envelope;

interface DatabaseWriter
{
    public function beginTransaction(): void;

    public function store(Envelope $envelope): void;

    public function commitTransaction(): void;
}
