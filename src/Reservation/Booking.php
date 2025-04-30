<?php

namespace Reservation;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use Reservation\Command\CancelBooking;
use Reservation\Command\CreateBooking;
use Reservation\Command\RecordEstimatedCheckInTime;
use Reservation\Event\BookingCancelled;
use Reservation\Event\BookingCreated;
use Reservation\Event\EstimatedCheckInTimeRecorded;

class Booking implements AggregateRoot
{
    use AggregateRootBehaviour;

    private string $guestName;
    private string $roomType;
    private \DateTimeImmutable $checkInDate;
    private \DateTimeImmutable $checkOutDate;
    private bool $isConfirmed = false;
    private ?string $estimatedCheckInTime = null;
    private bool $isCancelled = false;
    private ?string $cancellationReason = null;
    private ?\DateTimeImmutable $cancelledAt = null;

    public static function createBooking(CreateBooking $command): self
    {
        $booking = new self($command->bookingId());
        
        $booking->recordThat(new BookingCreated(
            $command->bookingId()->toString(),
            $command->guestName(),
            $command->roomType(),
            $command->checkInDate()->format('Y-m-d'),
            $command->checkOutDate()->format('Y-m-d')
        ));
        
        return $booking;
    }

    public function recordEstimatedCheckInTime(RecordEstimatedCheckInTime $command): void
    {
        $this->recordThat(new EstimatedCheckInTimeRecorded(
            $this->aggregateRootId()->toString(),
            $command->estimatedCheckInTime()
        ));
    }

    public function cancelBooking(CancelBooking $command): void
    {
        if ($this->isCancelled) {
            throw new \DomainException('Booking is already cancelled');
        }

        $this->recordThat(new BookingCancelled(
            $this->aggregateRootId()->toString(),
            $command->cancellationReason()
        ));
    }

    public function applyBookingCreated(BookingCreated $event): void
    {
        $this->guestName = $event->guestName();
        $this->roomType = $event->roomType();
        $this->checkInDate = new \DateTimeImmutable($event->checkInDate());
        $this->checkOutDate = new \DateTimeImmutable($event->checkOutDate());
    }

    public function applyEstimatedCheckInTimeRecorded(EstimatedCheckInTimeRecorded $event): void
    {
        $this->estimatedCheckInTime = $event->estimatedCheckInTime();
    }

    public function applyBookingCancelled(BookingCancelled $event): void
    {
        $this->isCancelled = true;
        $this->cancellationReason = $event->cancellationReason();
        $this->cancelledAt = new \DateTimeImmutable($event->cancelledAt());
    }
    
    public function getGuestName(): string
    {
        return $this->guestName;
    }
    
    public function getRoomType(): string
    {
        return $this->roomType;
    }
    
    public function getCheckInDate(): \DateTimeImmutable
    {
        return $this->checkInDate;
    }
    
    public function getCheckOutDate(): \DateTimeImmutable
    {
        return $this->checkOutDate;
    }
    
    public function getEstimatedCheckInTime(): ?string
    {
        return $this->estimatedCheckInTime;
    }
    
    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }
} 