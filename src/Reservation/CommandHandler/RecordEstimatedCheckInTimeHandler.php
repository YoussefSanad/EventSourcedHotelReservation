<?php

namespace Reservation\CommandHandler;

use EventSauce\EventSourcing\AggregateRootRepository;
use Reservation\BookingRepository;
use Reservation\Command\RecordEstimatedCheckInTime;

class RecordEstimatedCheckInTimeHandler
{
    private BookingRepository $repository;

    public function __construct(BookingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(RecordEstimatedCheckInTime $command): void
    {
        $booking = $this->repository->retrieveBooking($command->bookingId());
        $booking->recordEstimatedCheckInTime($command);
        $this->repository->storeBooking($booking);
    }
} 