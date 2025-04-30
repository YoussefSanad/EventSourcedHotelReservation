<?php

namespace Finance\ReadModel;

interface InvoiceReadModelRepository
{
    public function save(InvoiceReadModel $invoice): void;
    
    public function findById(string $id): ?InvoiceReadModel;
    
    public function findByBookingId(string $bookingId): array;
    
    public function findAllPending(): array;
    
    public function findByGuestName(string $guestName): array;
} 