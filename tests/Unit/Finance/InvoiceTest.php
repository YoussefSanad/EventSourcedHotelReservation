<?php

namespace Tests\Unit\Finance;

use Finance\Command\CreateInvoice;
use Finance\Event\InvoiceCreated;
use Finance\Invoice;
use Finance\InvoiceId;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    /** @test */
    public function it_creates_an_invoice(): void
    {
        // Given
        $invoiceId = InvoiceId::generate();
        $bookingId = 'test-booking-id';
        $guestName = 'John Doe';
        $roomType = 'Deluxe';
        $roomNumber = '101';
        $checkInDate = new \DateTimeImmutable('2023-06-10');
        $checkOutDate = new \DateTimeImmutable('2023-06-15');
        $roomRate = 150.00;
        
        // When
        $command = new CreateInvoice(
            $invoiceId,
            $bookingId,
            $guestName,
            $roomType,
            $roomNumber,
            $checkInDate,
            $checkOutDate,
            $roomRate
        );
        
        $invoice = Invoice::createInvoice($command);
        
        // Calculate expected total amount
        $stayDuration = (int) $checkInDate->diff($checkOutDate)->format('%a');
        $expectedAmount = $roomRate * $stayDuration;
        
        // Then
        $this->assertEquals($bookingId, $invoice->getBookingId());
        $this->assertEquals($guestName, $invoice->getGuestName());
        $this->assertEquals($roomType, $invoice->getRoomType());
        $this->assertEquals($roomNumber, $invoice->getRoomNumber());
        $this->assertEquals($checkInDate->format('Y-m-d'), $invoice->getCheckInDate()->format('Y-m-d'));
        $this->assertEquals($checkOutDate->format('Y-m-d'), $invoice->getCheckOutDate()->format('Y-m-d'));
        $this->assertEquals($expectedAmount, $invoice->getTotalAmount());
        $this->assertEquals('pending', $invoice->getStatus());
    }
} 