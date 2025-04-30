<?php

namespace Finance\ReadModel;

class InvoiceReadModel
{
    private string $id;
    private string $bookingId;
    private string $guestName;
    private string $roomType;
    private string $roomNumber;
    private string $checkInDate;
    private string $checkOutDate;
    private float $totalAmount;
    private string $status;
    private ?string $paidAt;
    private string $createdAt;

    public function __construct(
        string $id,
        string $bookingId,
        string $guestName,
        string $roomType,
        string $roomNumber,
        string $checkInDate,
        string $checkOutDate,
        float $totalAmount,
        string $status = 'pending',
        ?string $paidAt = null,
        string $createdAt = null
    ) {
        $this->id = $id;
        $this->bookingId = $bookingId;
        $this->guestName = $guestName;
        $this->roomType = $roomType;
        $this->roomNumber = $roomNumber;
        $this->checkInDate = $checkInDate;
        $this->checkOutDate = $checkOutDate;
        $this->totalAmount = $totalAmount;
        $this->status = $status;
        $this->paidAt = $paidAt;
        $this->createdAt = $createdAt ?? (new \DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBookingId(): string
    {
        return $this->bookingId;
    }

    public function getGuestName(): string
    {
        return $this->guestName;
    }

    public function getRoomType(): string
    {
        return $this->roomType;
    }

    public function getRoomNumber(): string
    {
        return $this->roomNumber;
    }

    public function getCheckInDate(): string
    {
        return $this->checkInDate;
    }

    public function getCheckOutDate(): string
    {
        return $this->checkOutDate;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaidAt(): ?string
    {
        return $this->paidAt;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function markAsPaid(string $paidAt = null): void
    {
        $this->status = 'paid';
        $this->paidAt = $paidAt ?? (new \DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    public function markAsCancelled(): void
    {
        $this->status = 'cancelled';
    }
} 