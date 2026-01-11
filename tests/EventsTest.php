<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\Envelope;

#[CoversClass(Events::class)]
final class EventsTest extends TestCase
{
    public function test_can_be_created_from_empty_input(): void
    {
        $events = Events::from();

        $this->assertCount(0, $events);
        $this->assertSame([], $events->envelopes());
        $this->assertSame([], $events->asArray());
    }

    public function test_can_be_created_from_envelopes(): void
    {
        $event1 = new TestEvent('one');
        $event2 = new TestEvent('two');

        $envelope1 = Envelope::from($event1);
        $envelope2 = Envelope::from($event2);

        $events = Events::from($envelope1, $envelope2);

        $this->assertCount(2, $events);
        $this->assertSame([$envelope1, $envelope2], $events->envelopes());
        $this->assertSame([$event1, $event2], $events->asArray());
    }

    public function test_lastEventId_returns_id_of_last_event(): void
    {
        $envelope1 = Envelope::from(new TestEvent('one'));
        $envelope2 = Envelope::from(new TestEvent('two'));

        $events = Events::from($envelope1, $envelope2);

        $this->assertSame($envelope2->eventId(), $events->lastEventId());
    }

    public function test_lastEventId_throws_exception_when_no_events_present(): void
    {
        $events = Events::from();

        $this->expectException(NoEventsException::class);

        $events->lastEventId();
    }

    public function test_is_iterable_over_events(): void
    {
        $event1 = new TestEvent('one');
        $event2 = new TestEvent('two');

        $events = Events::from(
            Envelope::from($event1),
            Envelope::from($event2)
        );

        $result = [];
        foreach ($events as $event) {
            $result[] = $event;
        }

        $this->assertSame([$event1, $event2], $result);
    }
}
