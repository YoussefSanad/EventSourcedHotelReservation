<?php

namespace Reservation\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class BookingCreated implements SerializablePayload
{
    public function __construct(
        private string $bookingId,
        private string $guestName,
        private string $roomType,
        private string $checkInDate,
        private string $checkOutDate
    ) {
    }

    public function bookingId(): string
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

    public function checkInDate(): string
    {
        return $this->checkInDate;
    }

    public function checkOutDate(): string
    {
        return $this->checkOutDate;
    }

    public function toPayload(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'guest_name' => $this->guestName,
            'room_type' => $this->roomType,
            'check_in_date' => $this->checkInDate,
            'check_out_date' => $this->checkOutDate
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new static(
            $payload['booking_id'],
            $payload['guest_name'],
            $payload['room_type'],
            $payload['check_in_date'],
            $payload['check_out_date']
        );
    }
} 