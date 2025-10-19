<?php declare(strict_types=1);

namespace spriebsch\sequora;

use RuntimeException;
use spriebsch\DomainEvent\EventId;

final class FailedToStoreEventForUnknownReasonException extends RuntimeException implements SequoraException
{
    public function __construct(EventId $eventId)
    {
        parent::__construct(
            sprintf(
                'Failed to store event %s for unknown reason',
                $eventId->asString(),
            )
        );
    }
}
