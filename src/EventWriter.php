<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\DomainEvent;

interface EventWriter
{
    public function store(DomainEvent ...$events): void;
}
