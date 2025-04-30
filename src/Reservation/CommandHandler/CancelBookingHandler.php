<?php

namespace Reservation\CommandHandler;

use EventSauce\EventSourcing\AggregateRootRepository;
use Reservation\Booking;
use Reservation\BookingRepository;
use Reservation\Command\CancelBooking;

class CancelBookingHandler
{
    private AggregateRootRepository $repository;

    public function __construct(BookingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(CancelBooking $command): void
    {
        $booking = $this->repository->retrieve($command->bookingId());
        $booking->cancelBooking($command);
        $this->repository->persist($booking);
    }
} 