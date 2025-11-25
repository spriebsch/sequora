<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\EventId;

#[CoversClass(FailedToStoreEventForUnknownReasonException::class)]
final class FailedToStoreEventForUnknownReasonExceptionTest extends TestCase
{
    public function test_some(): void
    {
        $eventId = EventId::generate();
        $exception = new FailedToStoreEventForUnknownReasonException($eventId);

        $this->assertSame(
            sprintf('Failed to store event %s for unknown reason', $eventId->asString()),
            $exception->getMessage()
        );
    }
}
