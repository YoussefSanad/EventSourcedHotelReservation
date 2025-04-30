<?php

namespace Reservation\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final class BookingCancelled implements SerializablePayload
{
    private string $bookingId;
    private string $cancellationReason;
    private string $cancelledAt;

    public function __construct(
        string $bookingId,
        string $cancellationReason,
        string $cancelledAt = null
    ) {
        $this->bookingId = $bookingId;
        $this->cancellationReason = $cancellationReason;
        $this->cancelledAt = $cancelledAt ?? (new \DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    public function bookingId(): string
    {
        return $this->bookingId;
    }

    public function cancellationReason(): string
    {
        return $this->cancellationReason;
    }

    public function cancelledAt(): string
    {
        return $this->cancelledAt;
    }

    public function toPayload(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'cancellation_reason' => $this->cancellationReason,
            'cancelled_at' => $this->cancelledAt,
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new self(
            $payload['booking_id'],
            $payload['cancellation_reason'],
            $payload['cancelled_at']
        );
    }
} 