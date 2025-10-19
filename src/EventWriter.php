<?php declare(strict_types=1);

namespace spriebsch\eventstore;

interface EventWriter
{
    public function store(Events $events): void;
}
