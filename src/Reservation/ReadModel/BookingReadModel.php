<?php

namespace Reservation\ReadModel;

class BookingReadModel
{
    private string $id;
    private string $guestName;
    private string $roomType;
    private string $checkInDate;
    private string $checkOutDate;
    private ?string $estimatedCheckInTime;
    private bool $isCancelled;
    private ?string $cancellationReason;
    private ?string $cancelledAt;

    public function __construct(
        string $id,
        string $guestName,
        string $roomType,
        string $checkInDate,
        string $checkOutDate,
        ?string $estimatedCheckInTime = null,
        bool $isCancelled = false,
        ?string $cancellationReason = null, 
        ?string $cancelledAt = null
    ) {
        $this->id = $id;
        $this->guestName = $guestName;
        $this->roomType = $roomType;
        $this->checkInDate = $checkInDate;
        $this->checkOutDate = $checkOutDate;
        $this->estimatedCheckInTime = $estimatedCheckInTime;
        $this->isCancelled = $isCancelled;
        $this->cancellationReason = $cancellationReason;
        $this->cancelledAt = $cancelledAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGuestName(): string
    {
        return $this->guestName;
    }

    public function getRoomType(): string
    {
        return $this->roomType;
    }

    public function getCheckInDate(): string
    {
        return $this->checkInDate;
    }

    public function getCheckOutDate(): string
    {
        return $this->checkOutDate;
    }

    public function getEstimatedCheckInTime(): ?string
    {
        return $this->estimatedCheckInTime;
    }

    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function getCancelledAt(): ?string
    {
        return $this->cancelledAt;
    }

    public function setEstimatedCheckInTime(?string $estimatedCheckInTime): void
    {
        $this->estimatedCheckInTime = $estimatedCheckInTime;
    }

    public function cancel(string $cancellationReason, string $cancelledAt): void
    {
        $this->isCancelled = true;
        $this->cancellationReason = $cancellationReason;
        $this->cancelledAt = $cancelledAt;
    }
} 