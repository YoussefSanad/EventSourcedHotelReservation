<?php

namespace Finance\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final class InvoiceCreated implements SerializablePayload
{
    private string $invoiceId;
    private string $bookingId;
    private string $guestName;
    private string $roomType;
    private string $roomNumber;
    private string $checkInDate;
    private string $checkOutDate;
    private float $totalAmount;
    private string $createdAt;

    public function __construct(
        string $invoiceId,
        string $bookingId,
        string $guestName,
        string $roomType,
        string $roomNumber,
        string $checkInDate,
        string $checkOutDate,
        float $totalAmount,
        string $createdAt = null
    ) {
        $this->invoiceId = $invoiceId;
        $this->bookingId = $bookingId;
        $this->guestName = $guestName;
        $this->roomType = $roomType;
        $this->roomNumber = $roomNumber;
        $this->checkInDate = $checkInDate;
        $this->checkOutDate = $checkOutDate;
        $this->totalAmount = $totalAmount;
        $this->createdAt = $createdAt ?? (new \DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    public function invoiceId(): string
    {
        return $this->invoiceId;
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

    public function roomNumber(): string
    {
        return $this->roomNumber;
    }

    public function checkInDate(): string
    {
        return $this->checkInDate;
    }

    public function checkOutDate(): string
    {
        return $this->checkOutDate;
    }

    public function totalAmount(): float
    {
        return $this->totalAmount;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function toPayload(): array
    {
        return [
            'invoice_id' => $this->invoiceId,
            'booking_id' => $this->bookingId,
            'guest_name' => $this->guestName,
            'room_type' => $this->roomType,
            'room_number' => $this->roomNumber,
            'check_in_date' => $this->checkInDate,
            'check_out_date' => $this->checkOutDate,
            'total_amount' => $this->totalAmount,
            'created_at' => $this->createdAt,
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new self(
            $payload['invoice_id'],
            $payload['booking_id'],
            $payload['guest_name'],
            $payload['room_type'],
            $payload['room_number'],
            $payload['check_in_date'],
            $payload['check_out_date'],
            $payload['total_amount'],
            $payload['created_at']
        );
    }
} 