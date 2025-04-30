<?php

namespace Tests\Unit\Reservation;

use Reservation\BookingId;
use Reservation\Command\CancelBooking;
use Reservation\Command\CreateBooking;
use Reservation\Event\BookingCancelled;
use Reservation\Event\BookingCreated;

class CancelBookingTest extends BookingTestCase
{
    /** @test */
    public function it_cancels_a_booking(): void
    {
        // Given - a booking exists
        $bookingId = $this->aggregateRootId();
        $guestName = 'John Doe';
        $roomType = 'Deluxe';
        $checkInDate = new \DateTimeImmutable('2023-05-01');
        $checkOutDate = new \DateTimeImmutable('2023-05-05');
        
        $this->given(
            new BookingCreated(
                $bookingId->toString(),
                $guestName,
                $roomType,
                $checkInDate->format('Y-m-d'),
                $checkOutDate->format('Y-m-d')
            )
        );
        
        // When - a CancelBooking command is handled
        $cancellationReason = 'Change of plans';
        $this->when(
            new CancelBooking(
                $bookingId,
                $cancellationReason
            )
        );
        
        // Then - a BookingCancelled event should be recorded
        $this->then(
            new BookingCancelled(
                $bookingId->toString(),
                $cancellationReason
            )
        );
    }

    /** @test */
    public function it_throws_exception_when_cancelling_already_cancelled_booking(): void
    {
        // Given - a booking exists and is already cancelled
        $bookingId = $this->aggregateRootId();
        $guestName = 'Jane Smith';
        $roomType = 'Standard';
        $checkInDate = new \DateTimeImmutable('2023-06-10');
        $checkOutDate = new \DateTimeImmutable('2023-06-15');
        $initialCancellationReason = 'Initial cancellation';
        
        $this->given(
            new BookingCreated(
                $bookingId->toString(),
                $guestName,
                $roomType,
                $checkInDate->format('Y-m-d'),
                $checkOutDate->format('Y-m-d')
            ),
            new BookingCancelled(
                $bookingId->toString(),
                $initialCancellationReason
            )
        );
        
        // When - trying to cancel an already cancelled booking and expect to fail
        $this->when(
            new CancelBooking(
                $bookingId,
                'Another cancellation reason'
            )
        )->expectToFail(new \DomainException('Booking is already cancelled'));
    }
} 