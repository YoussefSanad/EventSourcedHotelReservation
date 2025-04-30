<?php

namespace Finance;

use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageRepository;

class TransactionalInvoiceRepository extends InvoiceRepository
{
    private $transactionalDispatcher;

    public function __construct(
        MessageRepository $messageRepository,
        MessageDecorator $messageDecorator,
        $transactionalDispatcher
    ) {
        parent::__construct(
            $messageRepository,
            $messageDecorator,
            $transactionalDispatcher
        );
        
        $this->transactionalDispatcher = $transactionalDispatcher;
    }

    public function retrieveInvoice(InvoiceId $id): Invoice
    {
        return $this->retrieve($id);
    }

    public function storeInvoice(Invoice $invoice): void
    {
        try {
            $this->transactionalDispatcher->beginTransaction();
            $this->persist($invoice);
            $this->transactionalDispatcher->commitTransaction();
        } catch (\Throwable $e) {
            $this->transactionalDispatcher->rollbackTransaction();
            throw $e;
        }
    }
} 