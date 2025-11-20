<?php declare(strict_types=1);

namespace spriebsch\sequora;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use spriebsch\DomainEvent\CausationId;
use spriebsch\DomainEvent\Topic;

#[CoversClass(EventQuery::class)]
#[CoversClass(EventQuerySqliteSqlBuilder::class)]
final class EventQueryTest extends TestCase
{
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
        $query = EventQuery::from(['topics' => [$topic1]]);

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
}
