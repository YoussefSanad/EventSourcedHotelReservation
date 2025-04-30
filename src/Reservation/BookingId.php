<?php

namespace Reservation;

use EventSauce\EventSourcing\AggregateRootId;
use Ramsey\Uuid\Uuid;

class BookingId implements AggregateRootId
{
    private string $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $id): static
    {
        return new static($id);
    }

    public function toString(): string
    {
        return $this->id;
    }
} 