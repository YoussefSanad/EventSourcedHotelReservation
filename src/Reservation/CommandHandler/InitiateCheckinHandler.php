<?php

namespace Reservation\CommandHandler;

use EventSauce\EventSourcing\AggregateRootRepository;
use Reservation\BookingRepository;
use Reservation\Command\InitiateCheckin;

class InitiateCheckinHandler
{
    private AggregateRootRepository $repository;

    public function __construct(BookingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(InitiateCheckin $command): void
    {
        $booking = $this->repository->retrieve($command->bookingId());
        $booking->initiateCheckin($command);
        $this->repository->persist($booking);
    }
} 