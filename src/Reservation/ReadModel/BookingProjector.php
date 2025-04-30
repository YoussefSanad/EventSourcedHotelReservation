<?php

namespace Reservation\ReadModel;

use EventSauce\EventSourcing\Message;
use Reservation\Event\BookingCancelled;
use Reservation\Event\BookingCreated;
use Reservation\Event\CheckinInitiated;
use Reservation\Event\EstimatedCheckInTimeRecorded;

class BookingProjector
{
    private BookingReadModelRepository $repository;

    public function __construct(BookingReadModelRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handleMessage(Message $message): void
    {
        $event = $message->payload();

        if ($event instanceof BookingCreated) {
            $this->whenBookingCreated($event);
        } elseif ($event instanceof EstimatedCheckInTimeRecorded) {
            $this->whenEstimatedCheckInTimeRecorded($event);
        } elseif ($event instanceof BookingCancelled) {
            $this->whenBookingCancelled($event);
        } elseif ($event instanceof CheckinInitiated) {
            $this->whenCheckinInitiated($event);
        }
    }

    private function whenBookingCreated(BookingCreated $event): void
    {
        $booking = new BookingReadModel(
            $event->bookingId(),
            $event->guestName(),
            $event->roomType(),
            $event->checkInDate(),
            $event->checkOutDate()
        );

        $this->repository->save($booking);
    }

    private function whenEstimatedCheckInTimeRecorded(EstimatedCheckInTimeRecorded $event): void
    {
        $booking = $this->repository->findById($event->bookingId());
        if ($booking === null) {
            return;
        }

        $booking->setEstimatedCheckInTime($event->estimatedCheckInTime());
        $this->repository->save($booking);
    }

    private function whenBookingCancelled(BookingCancelled $event): void
    {
        $booking = $this->repository->findById($event->bookingId());
        if ($booking === null) {
            return;
        }

        $booking->cancel($event->cancellationReason(), $event->cancelledAt());
        $this->repository->save($booking);
    }

    private function whenCheckinInitiated(CheckinInitiated $event): void
    {
        $booking = $this->repository->findById($event->bookingId());
        if ($booking === null) {
            return;
        }

        $booking->checkin(
            $event->actualArrivalTime(),
            $event->roomNumber(),
            $event->specialRequests(),
            $event->initiatedAt()
        );
        $this->repository->save($booking);
    }
} 