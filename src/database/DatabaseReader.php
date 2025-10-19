<?php declare(strict_types=1);

namespace spriebsch\sequora;

interface DatabaseReader
{
    public function all(): Events;
}
