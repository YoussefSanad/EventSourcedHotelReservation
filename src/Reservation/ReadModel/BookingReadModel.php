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
    private bool $isCheckedIn;
    private ?string $actualArrivalTime;
    private ?string $roomNumber;
    private ?array $specialRequests;
    private ?string $checkinTime;

    public function __construct(
        string $id,
        string $guestName,
        string $roomType,
        string $checkInDate,
        string $checkOutDate,
        ?string $estimatedCheckInTime = null,
        bool $isCancelled = false,
        ?string $cancellationReason = null, 
        ?string $cancelledAt = null,
        bool $isCheckedIn = false,
        ?string $actualArrivalTime = null,
        ?string $roomNumber = null,
        ?array $specialRequests = null,
        ?string $checkinTime = null
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
        $this->isCheckedIn = $isCheckedIn;
        $this->actualArrivalTime = $actualArrivalTime;
        $this->roomNumber = $roomNumber;
        $this->specialRequests = $specialRequests;
        $this->checkinTime = $checkinTime;
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

    public function isCheckedIn(): bool
    {
        return $this->isCheckedIn;
    }

    public function getActualArrivalTime(): ?string
    {
        return $this->actualArrivalTime;
    }

    public function getRoomNumber(): ?string
    {
        return $this->roomNumber;
    }

    public function getSpecialRequests(): ?array
    {
        return $this->specialRequests;
    }

    public function getCheckinTime(): ?string
    {
        return $this->checkinTime;
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

    public function checkin(string $actualArrivalTime, ?string $roomNumber, ?array $specialRequests, string $checkinTime): void
    {
        $this->isCheckedIn = true;
        $this->actualArrivalTime = $actualArrivalTime;
        $this->roomNumber = $roomNumber;
        $this->specialRequests = $specialRequests;
        $this->checkinTime = $checkinTime;
    }
} 