<?php

namespace Finance\ReadModel;

use EventSauce\EventSourcing\Message;
use Finance\Event\InvoiceCreated;

class InvoiceProjector
{
    private InvoiceReadModelRepository $repository;

    public function __construct(InvoiceReadModelRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handleMessage(Message $message): void
    {
        $event = $message->payload();

        if ($event instanceof InvoiceCreated) {
            $this->whenInvoiceCreated($event);
        }
    }

    private function whenInvoiceCreated(InvoiceCreated $event): void
    {
        $invoice = new InvoiceReadModel(
            $event->invoiceId(),
            $event->bookingId(),
            $event->guestName(),
            $event->roomType(),
            $event->roomNumber(),
            $event->checkInDate(),
            $event->checkOutDate(),
            $event->totalAmount(),
            'pending',
            null,
            $event->createdAt()
        );

        $this->repository->save($invoice);
    }
} 