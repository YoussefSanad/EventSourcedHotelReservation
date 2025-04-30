<?php

namespace Reservation\Command;

use Reservation\BookingId;

class CreateBooking
{
    public function __construct(
        private BookingId $bookingId,
        private string $guestName,
        private string $roomType,
        private \DateTimeImmutable $checkInDate,
        private \DateTimeImmutable $checkOutDate
    ) {
    }

    public function bookingId(): BookingId
    {
        return $this->bookingId;
    }

    public function guestName(): string
    {
        return $this->guestName;
    }

    public function roomType(): string
    {
        return $this->roomType;
    }

    public function checkInDate(): \DateTimeImmutable
    {
        return $this->checkInDate;
    }

    public function checkOutDate(): \DateTimeImmutable
    {
        return $this->checkOutDate;
    }
} 