<?php

namespace Reservation\CommandHandler;

use EventSauce\EventSourcing\AggregateRootRepository;
use Reservation\Booking;
use Reservation\BookingRepository;
use Reservation\Command\CreateBooking;

class CreateBookingHandler
{
    private BookingRepository $repository;

    public function __construct(BookingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(CreateBooking $command): void
    {
        $booking = Booking::createBooking($command);
        $this->repository->storeBooking($booking);
    }
} 