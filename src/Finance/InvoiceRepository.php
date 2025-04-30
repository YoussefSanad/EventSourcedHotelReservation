<?php

namespace Finance;

use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;

class InvoiceRepository extends EventSourcedAggregateRootRepository
{
    public function __construct(
        MessageRepository $messageRepository,
        MessageDecorator $messageDecorator,
        MessageDispatcher $dispatcher
    ) {
        parent::__construct(
            Invoice::class,
            $messageRepository,
            $dispatcher,
            $messageDecorator
        );
    }
    
    public function retrieveInvoice(InvoiceId $id): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = $this->retrieve($id);
        return $invoice;
    }
    
    public function storeInvoice(Invoice $invoice): void
    {
        $this->persist($invoice);
    }
} 