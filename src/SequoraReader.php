<?php declare(strict_types=1);

namespace spriebsch\sequora;

class SequoraReader implements EventReader
{
    private function __construct(private DatabaseReader $dbReader) {}

    public static function from(DatabaseReader $dbReader): self
    {
        return new self($dbReader);
    }

    public function all(): Events
    {
        return $this->dbReader->query(EventQuery::from());
    }

    public function query(EventQuery $query): Events
    {
        return $this->dbReader->query($query);
    }
}
