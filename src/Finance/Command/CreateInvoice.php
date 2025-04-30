<?php

namespace Finance\Command;

use Finance\InvoiceId;

class CreateInvoice
{
    private InvoiceId $invoiceId;
    private string $bookingId;
    private string $guestName;
    private string $roomType;
    private string $roomNumber;
    private \DateTimeImmutable $checkInDate;
    private \DateTimeImmutable $checkOutDate;
    private float $roomRate;
    private int $stayDuration;

    public function __construct(
        InvoiceId $invoiceId,
        string $bookingId,
        string $guestName,
        string $roomType,
        string $roomNumber,
        \DateTimeImmutable $checkInDate,
        \DateTimeImmutable $checkOutDate,
        float $roomRate
    ) {
        $this->invoiceId = $invoiceId;
        $this->bookingId = $bookingId;
        $this->guestName = $guestName;
        $this->roomType = $roomType;
        $this->roomNumber = $roomNumber;
        $this->checkInDate = $checkInDate;
        $this->checkOutDate = $checkOutDate;
        $this->roomRate = $roomRate;
        $this->stayDuration = $this->calculateStayDuration();
    }

    public function invoiceId(): InvoiceId
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

    public function checkInDate(): \DateTimeImmutable
    {
        return $this->checkInDate;
    }

    public function checkOutDate(): \DateTimeImmutable
    {
        return $this->checkOutDate;
    }

    public function roomRate(): float
    {
        return $this->roomRate;
    }

    public function calculateStayDuration(): int
    {
        $interval = $this->checkInDate->diff($this->checkOutDate);
        return (int) $interval->format('%a');
    }

    public function calculateTotalAmount(): float
    {
        return $this->roomRate * $this->stayDuration;
    }
} 