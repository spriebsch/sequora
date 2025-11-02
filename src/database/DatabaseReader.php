<?php declare(strict_types=1);

namespace spriebsch\sequora;

interface DatabaseReader
{
    public function query(EventQuery $query): Events;
}
