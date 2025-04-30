<?php

namespace Tests\Unit\Reservation;

use Reservation\Command\RecordEstimatedCheckInTime;
use Reservation\Event\BookingCreated;
use Reservation\Event\EstimatedCheckInTimeRecorded;

class RecordEstimatedCheckInTimeTest extends BookingTestCase
{
    /** @test */
    public function it_records_estimated_check_in_time(): void
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
        
        // When - the guest provides their estimated check-in time
        $estimatedCheckInTime = '14:00';
        
        $this->when(
            new RecordEstimatedCheckInTime(
                $bookingId,
                $estimatedCheckInTime
            )
        );
        
        // Then - an EstimatedCheckInTimeRecorded event should be recorded
        $this->then(
            new EstimatedCheckInTimeRecorded(
                $bookingId->toString(),
                $estimatedCheckInTime
            )
        );
    }
    
    /** @test */
    public function it_updates_estimated_check_in_time_when_changed(): void
    {
        // Given - a booking exists with an estimated check-in time
        $bookingId = $this->aggregateRootId();
        $guestName = 'Jane Smith';
        $roomType = 'Standard';
        $checkInDate = new \DateTimeImmutable('2023-06-10');
        $checkOutDate = new \DateTimeImmutable('2023-06-15');
        $initialEstimatedTime = '15:00';
        
        $this->given(
            new BookingCreated(
                $bookingId->toString(),
                $guestName,
                $roomType,
                $checkInDate->format('Y-m-d'),
                $checkOutDate->format('Y-m-d')
            ),
            new EstimatedCheckInTimeRecorded(
                $bookingId->toString(),
                $initialEstimatedTime
            )
        );
        
        // When - the guest updates their estimated check-in time
        $updatedEstimatedTime = '18:30';
        
        $this->when(
            new RecordEstimatedCheckInTime(
                $bookingId,
                $updatedEstimatedTime
            )
        );
        
        // Then - a new EstimatedCheckInTimeRecorded event should be recorded
        $this->then(
            new EstimatedCheckInTimeRecorded(
                $bookingId->toString(),
                $updatedEstimatedTime
            )
        );
    }
} 