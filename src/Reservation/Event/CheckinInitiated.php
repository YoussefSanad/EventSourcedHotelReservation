<?php

namespace Reservation\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final class CheckinInitiated implements SerializablePayload
{
    private string $bookingId;
    private string $actualArrivalTime;
    private ?string $roomNumber;
    private ?array $specialRequests;
    private string $initiatedAt;

    public function __construct(
        string $bookingId, 
        string $actualArrivalTime, 
        ?string $roomNumber = null,
        ?array $specialRequests = null,
        string $initiatedAt = null
    ) {
        $this->bookingId = $bookingId;
        $this->actualArrivalTime = $actualArrivalTime;
        $this->roomNumber = $roomNumber;
        $this->specialRequests = $specialRequests;
        $this->initiatedAt = $initiatedAt ?? (new \DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    public function bookingId(): string
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

    public function initiatedAt(): string
    {
        return $this->initiatedAt;
    }

    public function toPayload(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'actual_arrival_time' => $this->actualArrivalTime,
            'room_number' => $this->roomNumber,
            'special_requests' => $this->specialRequests,
            'initiated_at' => $this->initiatedAt,
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new self(
            $payload['booking_id'],
            $payload['actual_arrival_time'],
            $payload['room_number'],
            $payload['special_requests'],
            $payload['initiated_at']
        );
    }
} 