<?php declare(strict_types=1);

namespace spriebsch\sequora;

use RuntimeException;
use spriebsch\DomainEvent\EventId;
use Throwable;

final class FailedToStoreEventException extends RuntimeException implements SequoraException
{
    public function __construct(EventId $eventId, Throwable $exception)
    {
        parent::__construct(
            sprintf(
                'Failed to store event %s: %s',
                $eventId->asString(),
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }
}
