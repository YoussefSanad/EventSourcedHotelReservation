<?php

namespace Finance;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use Finance\Command\CreateInvoice;
use Finance\Event\InvoiceCreated;

class Invoice implements AggregateRoot
{
    use AggregateRootBehaviour;

    private string $bookingId;
    private string $guestName;
    private string $roomType;
    private string $roomNumber;
    private \DateTimeImmutable $checkInDate;
    private \DateTimeImmutable $checkOutDate;
    private float $totalAmount;
    private string $status = 'pending'; // pending, paid, cancelled
    private ?\DateTimeImmutable $paidAt = null;

    public static function createInvoice(CreateInvoice $command): self
    {
        $invoice = new self($command->invoiceId());
        
        $invoice->recordThat(new InvoiceCreated(
            $command->invoiceId()->toString(),
            $command->bookingId(),
            $command->guestName(),
            $command->roomType(),
            $command->roomNumber(),
            $command->checkInDate()->format('Y-m-d'),
            $command->checkOutDate()->format('Y-m-d'),
            $command->calculateTotalAmount()
        ));
        
        return $invoice;
    }

    public function applyInvoiceCreated(InvoiceCreated $event): void
    {
        $this->bookingId = $event->bookingId();
        $this->guestName = $event->guestName();
        $this->roomType = $event->roomType();
        $this->roomNumber = $event->roomNumber();
        $this->checkInDate = new \DateTimeImmutable($event->checkInDate());
        $this->checkOutDate = new \DateTimeImmutable($event->checkOutDate());
        $this->totalAmount = $event->totalAmount();
    }
    
    public function getBookingId(): string
    {
        return $this->bookingId;
    }
    
    public function getGuestName(): string
    {
        return $this->guestName;
    }
    
    public function getRoomType(): string
    {
        return $this->roomType;
    }
    
    public function getRoomNumber(): string
    {
        return $this->roomNumber;
    }
    
    public function getCheckInDate(): \DateTimeImmutable
    {
        return $this->checkInDate;
    }
    
    public function getCheckOutDate(): \DateTimeImmutable
    {
        return $this->checkOutDate;
    }
    
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }
    
    public function getStatus(): string
    {
        return $this->status;
    }
    
    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }
} 