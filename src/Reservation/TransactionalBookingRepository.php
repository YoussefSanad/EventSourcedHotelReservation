<?php

namespace Reservation;

use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageRepository;

class TransactionalBookingRepository extends BookingRepository
{
    private TransactionalMessageDispatcher $transactionalDispatcher;

    public function __construct(
        MessageRepository $messageRepository,
        MessageDecorator $messageDecorator,
        TransactionalMessageDispatcher $transactionalDispatcher
    ) {
        parent::__construct(
            $messageRepository,
            $messageDecorator,
            $transactionalDispatcher
        );
        
        $this->transactionalDispatcher = $transactionalDispatcher;
    }

    public function retrieveBooking(BookingId $id): Booking
    {
        return $this->retrieve($id);
    }

    public function storeBooking(Booking $booking): void
    {
        try {
            $this->transactionalDispatcher->beginTransaction();
            $this->persist($booking);
            $this->transactionalDispatcher->commitTransaction();
        } catch (\Throwable $e) {
            $this->transactionalDispatcher->rollbackTransaction();
            throw $e;
        }
    }
} 