<?php declare(strict_types=1);

namespace spriebsch\sequora;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\CausationId;
use spriebsch\DomainEvent\EventId;
use spriebsch\DomainEvent\Topic;

#[CoversClass(EventQuery::class)]
#[CoversClass(EventQuerySqliteSqlBuilder::class)]
final class EventQueryTest extends TestCase
{
    public function test_with_limit(): void
    {
        $limit = 25;
        $query = EventQuery::from();

        $query = $query->limit($limit);

        $this->assertEquals($limit, $query->limitValue());
    }

    public function test_one_topic(): void
    {
        $topic = Topic::fromString('the-vendor.the-domain.the-context.the-name');
        $query = EventQuery::from();

        $query = $query->withTopics($topic);

        $this->assertEquals([$topic], $query->topicsValue());
    }

    public function test_append_topics(): void
    {
        $topic1 = Topic::fromString('the-vendor.the-domain.the-context.the-name-1');
        $topic2 = Topic::fromString('the-vendor.the-domain.the-context.the-name-2');

        $query = EventQuery::from();
        $query = $query->withTopics($topic1);
        $query = $query->withTopics($topic2);

        $this->assertEquals([$topic1, $topic2], $query->topicsValue());
    }

    public function test_append_topics_is_idempotent(): void
    {
        $topic1 = Topic::fromString('the-vendor.the-domain.the-context.the-name-1');
        $topic2 = Topic::fromString('the-vendor.the-domain.the-context.the-name-2');

        $query = EventQuery::from();
        $query = $query->withTopics($topic1);
        $query = $query->withTopics($topic2);
        $query = $query->withTopics($topic2);

        $this->assertEquals([$topic1, $topic2], $query->topicsValue());
    }

    public function test_one_correlation_id(): void
    {
        $correlationId = TestId::generate();
        $query = EventQuery::from();

        $query = $query->withCorrelationId($correlationId);

        $this->assertEquals([$correlationId], $query->correlationIdValues());
    }

    public function test_multiple_correlation_ids(): void
    {
        $correlationId1 = TestId::generate();
        $correlationId2 = TestId::generate();
        $query = EventQuery::from();

        $query = $query->withCorrelationId($correlationId1)->withCorrelationId($correlationId2);

        $this->assertEquals([$correlationId1, $correlationId2], $query->correlationIdValues());
    }

    public function test_with_causation_id(): void
    {
        $causationId = CausationId::generate();
        $query = EventQuery::from();

        $query = $query->withCausationId($causationId);

        $this->assertEquals($causationId, $query->causationIdValue());
    }

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
