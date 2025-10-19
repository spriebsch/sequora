<?php declare(strict_types=1);

namespace spriebsch\sequora;

use RuntimeException;

final class NoEventsException extends RuntimeException implements SequoraException
{
    public function __construct()
    {
        parent::__construct(
            'No events, cannot retrieve lastEventId'
        );
    }
}
