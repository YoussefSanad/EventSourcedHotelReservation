<?php

namespace Reservation;

use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;

class BookingRepository extends EventSourcedAggregateRootRepository
{
    public function __construct(
        MessageRepository $messageRepository,
        MessageDecorator $messageDecorator,
        MessageDispatcher $dispatcher
    ) {
        parent::__construct(
            Booking::class,
            $messageRepository,
            $dispatcher,
            $messageDecorator
        );
    }
    
    public function retrieveBooking(BookingId $id): Booking
    {
        /** @var Booking $booking */
        $booking = $this->retrieve($id);
        return $booking;
    }
    
    public function storeBooking(Booking $booking): void
    {
        $this->persist($booking);
    }
} 