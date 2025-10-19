<?php declare(strict_types=1);

namespace spriebsch\sequora;

interface EventReader
{
    public function all(): Events;
}
