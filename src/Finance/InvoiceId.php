<?php

namespace Finance;

use EventSauce\EventSourcing\AggregateRootId;
use Ramsey\Uuid\Uuid;

class InvoiceId implements AggregateRootId
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
        return new self($id);
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
} 