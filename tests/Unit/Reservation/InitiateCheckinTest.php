<?php

namespace Tests\Unit\Reservation;

use Reservation\Command\InitiateCheckin;
use Reservation\Event\BookingCreated;
use Reservation\Event\CheckinInitiated;

class InitiateCheckinTest extends BookingTestCase
{
    /** @test */
    public function it_initiates_checkin_for_a_booking(): void
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
        
        // When - an InitiateCheckin command is handled
        $actualArrivalTime = '14:30';
        $roomNumber = '505';
        $specialRequests = ['Extra pillows', 'Quiet room'];
        
        $this->when(
            new InitiateCheckin(
                $bookingId,
                $actualArrivalTime,
                $roomNumber,
                $specialRequests
            )
        );
        
        // Then - a CheckinInitiated event should be recorded
        // Note: We can't assert on the exact initiatedAt timestamp since it's generated at runtime
        $this->then(
            new CheckinInitiated(
                $bookingId->toString(),
                $actualArrivalTime,
                $roomNumber,
                $specialRequests
            )
        );
    }

    /** @test */
    public function it_throws_exception_when_checking_in_already_checked_in_booking(): void
    {
        // Given - a booking exists and is already checked in
        $bookingId = $this->aggregateRootId();
        $guestName = 'Jane Smith';
        $roomType = 'Standard';
        $checkInDate = new \DateTimeImmutable('2023-06-10');
        $checkOutDate = new \DateTimeImmutable('2023-06-15');
        $initialArrivalTime = '15:00';
        $initialRoomNumber = '202';
        
        $this->given(
            new BookingCreated(
                $bookingId->toString(),
                $guestName,
                $roomType,
                $checkInDate->format('Y-m-d'),
                $checkOutDate->format('Y-m-d')
            ),
            new CheckinInitiated(
                $bookingId->toString(),
                $initialArrivalTime,
                $initialRoomNumber
            )
        );
        
        // When - trying to check in an already checked-in booking and expect to fail
        $this->when(
            new InitiateCheckin(
                $bookingId,
                '16:00',
                '303'
            )
        )->expectToFail(new \DomainException('Guest has already checked in'));
    }
} 