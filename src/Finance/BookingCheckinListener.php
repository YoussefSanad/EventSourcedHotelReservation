<?php

namespace Finance;

use Common\CommandBus\CommandBus;
use EventSauce\EventSourcing\Message;
use Finance\Command\CreateInvoice;
use Reservation\Event\CheckinInitiated;
use Reservation\BookingRepository;
use Reservation\BookingId;

class BookingCheckinListener
{
    private ?CommandBus $commandBus = null;
    private BookingRepository $bookingRepository;
    private array $roomRates = [
        'Standard' => 100.00,
        'Deluxe' => 150.00,
        'Suite' => 250.00,
        'Deluxe Suite' => 300.00,
        'Presidential Suite' => 500.00
    ];

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }
    
    public function setCommandBus(CommandBus $commandBus): void
    {
        $this->commandBus = $commandBus;
    }

    public function handleMessage(Message $message): void
    {
        $event = $message->payload();

        if ($event instanceof CheckinInitiated) {
            $this->whenCheckinInitiated($event);
        }
    }

    private function whenCheckinInitiated(CheckinInitiated $event): void
    {
        if ($this->commandBus === null) {
            // Cannot process without a command bus
            return;
        }
        
        // Retrieve the booking to get complete information
        $bookingId = BookingId::fromString($event->bookingId());
        $booking = $this->bookingRepository->retrieveBooking($bookingId);
        
        // Generate invoice ID
        $invoiceId = InvoiceId::generate();
        
        // Get room rate based on room type (default to 100 if not found)
        $roomType = $booking->getRoomType();
        $roomRate = $this->roomRates[$roomType] ?? 100.00;
        
        // Create invoice command
        $createInvoiceCommand = new CreateInvoice(
            $invoiceId,
            $event->bookingId(),
            $booking->getGuestName(),
            $roomType,
            $event->roomNumber() ?? 'Not assigned',
            $booking->getCheckInDate(),
            $booking->getCheckOutDate(),
            $roomRate
        );
        
        // Dispatch the command
        $this->commandBus->handle($createInvoiceCommand);
    }
} 