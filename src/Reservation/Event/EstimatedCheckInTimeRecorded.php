<?php

namespace Reservation\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class EstimatedCheckInTimeRecorded implements SerializablePayload
{
    public function __construct(
        private string $bookingId,
        private string $estimatedCheckInTime
    ) {
    }

    public function bookingId(): string
    {
        return $this->bookingId;
    }

    public function estimatedCheckInTime(): string
    {
        return $this->estimatedCheckInTime;
    }

    public function toPayload(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'estimated_check_in_time' => $this->estimatedCheckInTime
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new static(
            $payload['booking_id'],
            $payload['estimated_check_in_time']
        );
    }
} 