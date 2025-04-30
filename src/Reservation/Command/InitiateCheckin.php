<?php

namespace Reservation\Command;

use Reservation\BookingId;

final class InitiateCheckin
{
    private BookingId $bookingId;
    private string $actualArrivalTime;
    private ?string $roomNumber;
    private ?array $specialRequests;

    public function __construct(
        BookingId $bookingId, 
        string $actualArrivalTime, 
        ?string $roomNumber = null,
        ?array $specialRequests = null
    ) {
        $this->bookingId = $bookingId;
        $this->actualArrivalTime = $actualArrivalTime;
        $this->roomNumber = $roomNumber;
        $this->specialRequests = $specialRequests;
    }

    public function bookingId(): BookingId
    {
        return $this->bookingId;
    }

    public function actualArrivalTime(): string
    {
        return $this->actualArrivalTime;
    }

    public function roomNumber(): ?string
    {
        return $this->roomNumber;
    }

    public function specialRequests(): ?array
    {
        return $this->specialRequests;
    }
} 