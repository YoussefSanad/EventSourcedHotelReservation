<?php

namespace Reservation\Command;

use Reservation\BookingId;

final class CancelBooking
{
    private BookingId $bookingId;
    private string $cancellationReason;

    public function __construct(BookingId $bookingId, string $cancellationReason)
    {
        $this->bookingId = $bookingId;
        $this->cancellationReason = $cancellationReason;
    }

    public function bookingId(): BookingId
    {
        return $this->bookingId;
    }

    public function cancellationReason(): string
    {
        return $this->cancellationReason;
    }
} 