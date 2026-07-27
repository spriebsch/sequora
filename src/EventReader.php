<?php declare(strict_types=1);

namespace spriebsch\sequora;

interface EventReader
{
    public function query(EventQuery $query): Events;

    public function all(): Events;
}
