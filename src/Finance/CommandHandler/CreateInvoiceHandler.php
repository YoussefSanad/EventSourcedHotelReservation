<?php

namespace Finance\CommandHandler;

use EventSauce\EventSourcing\AggregateRootRepository;
use Finance\Command\CreateInvoice;
use Finance\Invoice;
use Finance\InvoiceRepository;

class CreateInvoiceHandler
{
    private AggregateRootRepository $repository;

    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(CreateInvoice $command): void
    {
        $invoice = Invoice::createInvoice($command);
        $this->repository->persist($invoice);
    }
} 