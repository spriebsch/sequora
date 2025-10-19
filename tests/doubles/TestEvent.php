<?php declare(strict_types=1);

namespace spriebsch\sequora;

use spriebsch\DomainEvent\DomainEvent;
use spriebsch\DomainEvent\MapToTopic;

#[MapToTopic('spriebsch.sequora.test.event')]
class TestEvent implements DomainEvent
{
}
