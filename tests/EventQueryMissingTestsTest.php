<?php declare(strict_types=1);

namespace spriebsch\sequora;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;

#[CoversClass(EventQuery::class)]
#[UsesClass(EventQuerySqliteSqlBuilder::class)]
final class EventQueryMissingTestsTest extends TestCase
{
    public function test_starting_after_sets_and_returns_event_id(): void
    {
        $eventId = EventId::generate();

        $query = EventQuery::from()->after($eventId);

        $this->assertNotNull($query->afterValue());
        $this->assertTrue($eventId->equals($query->afterValue()));
    }

    public function test_from_rejects_unknown_condition_keys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown condition key(s): invalid');

        EventQuery::from(['invalid' => 'x']);
    }

    public function test_after_event_id_key_is_allowed_in_constructor(): void
    {
        $eventId = EventId::generate();

        $query = EventQuery::from(['afterEventId' => $eventId]);

        $this->assertNotNull($query->afterValue());
        $this->assertTrue($eventId->equals($query->afterValue()));
    }

    public function test_with_topics_and_starting_after_both_preserved(): void
    {
        $eventId = EventId::generate();
        $topic = Topic::fromString('the-vendor.the-domain.the-context.the-name');

        $query = EventQuery::from()->withTopics($topic)->after($eventId);

        $this->assertSame([$topic], $query->topicsValue());
        $this->assertTrue($eventId->equals($query->afterValue()));
    }
}
