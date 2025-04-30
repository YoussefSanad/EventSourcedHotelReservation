<?php

namespace Reservation\Command;

use Reservation\BookingId;

class RecordEstimatedCheckInTime
{
    public function __construct(
        private BookingId $bookingId,
        private string $estimatedCheckInTime
    ) {
    }

    public function bookingId(): BookingId
    {
        return $this->bookingId;
    }

    public function estimatedCheckInTime(): string
    {
        return $this->estimatedCheckInTime;
    }
} 