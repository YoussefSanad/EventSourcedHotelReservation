<?php

namespace Tests\Unit\Reservation;

use Common\CommandBus\CommandBus;
use PHPUnit\Framework\TestCase;
use Reservation\Booking;
use Reservation\BookingId;
use Reservation\Command\CreateBooking;
use Reservation\Event\BookingCreated;
use EventSauce\EventSourcing\TestUtilities\AggregateRootTestCase;
use EventSauce\EventSourcing\AggregateRootId;

class CreateBookingTest extends BookingTestCase
{
    /** @test */
    public function it_creates_a_booking(): void
    {
        // Given - no prior events
        
        // When - a CreateBooking command is handled
        $bookingId = $this->aggregateRootId();
        $guestName = 'John Doe';
        $roomType = 'Deluxe';
        $checkInDate = new \DateTimeImmutable('2023-05-01');
        $checkOutDate = new \DateTimeImmutable('2023-05-05');
        
        $this->when(
            new CreateBooking(
                $bookingId,
                $guestName,
                $roomType,
                $checkInDate,
                $checkOutDate
            )
        );
        
        // Then - a BookingCreated event should be recorded
        $this->then(
            new BookingCreated(
                $bookingId->toString(),
                $guestName,
                $roomType,
                $checkInDate->format('Y-m-d'),
                $checkOutDate->format('Y-m-d')
            )
        );
    }
    
    /** @test */
    public function it_handles_creating_a_booking_with_valid_dates(): void
    {
        // Given - no prior events
        
        // When - a CreateBooking command is handled
        $bookingId = $this->aggregateRootId();
        $guestName = 'Jane Smith';
        $roomType = 'Standard';
        $checkInDate = new \DateTimeImmutable('2023-06-10');
        $checkOutDate = new \DateTimeImmutable('2023-06-15');
        
        $this->when(
            new CreateBooking(
                $bookingId,
                $guestName,
                $roomType,
                $checkInDate,
                $checkOutDate
            )
        );
        
        // Then - a BookingCreated event should be recorded
        $this->then(
            new BookingCreated(
                $bookingId->toString(),
                $guestName,
                $roomType,
                $checkInDate->format('Y-m-d'),
                $checkOutDate->format('Y-m-d')
            )
        );
    }
} 