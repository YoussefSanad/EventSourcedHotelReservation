<?php

namespace Tests\Unit\Reservation;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Reservation\BookingId;
use Reservation\Command\CancelBooking;
use Reservation\Command\CreateBooking;
use Reservation\Command\RecordEstimatedCheckInTime;
use Reservation\ReadModel\BookingReadModelRepository;

class ReadModelTest extends TestCase
{
    private Connection $connection;
    private BookingReadModelRepository $readModelRepository;
    private TestCommandBus $commandBus;

    protected function setUp(): void
    {
        // Initialize the container with test configuration
        $container = (require __DIR__ . '/../../../config/container_test.php')();
        
        // Get the connection and repositories
        $this->connection = $container->get(Connection::class);
        $this->readModelRepository = $container->get(BookingReadModelRepository::class);
        
        // Setup command bus with all handlers
        $this->commandBus = new TestCommandBus($container);
        
        // Clean tables before test
        $this->cleanTables();
    }

    private function cleanTables(): void
    {
        $this->connection->executeStatement('DELETE FROM booking_read_model');
        $this->connection->executeStatement('DELETE FROM event_store');
    }

    /** @test */
    public function it_creates_read_model_entry_when_booking_is_created(): void
    {
        // Given a booking ID
        $bookingId = BookingId::generate();
        $guestName = 'John Doe';
        $roomType = 'Deluxe';
        $checkInDate = new DateTimeImmutable('2023-05-01');
        $checkOutDate = new DateTimeImmutable('2023-05-05');
        
        // When a CreateBooking command is handled
        $createCommand = new CreateBooking(
            $bookingId,
            $guestName,
            $roomType,
            $checkInDate,
            $checkOutDate
        );
        
        $this->commandBus->handle($createCommand);
        
        // Then a read model entry should exist
        $readModel = $this->readModelRepository->findById($bookingId->toString());
        
        $this->assertNotNull($readModel);
        $this->assertEquals($bookingId->toString(), $readModel->getId());
        $this->assertEquals($guestName, $readModel->getGuestName());
        $this->assertEquals($roomType, $readModel->getRoomType());
        $this->assertEquals($checkInDate->format('Y-m-d'), $readModel->getCheckInDate());
        $this->assertEquals($checkOutDate->format('Y-m-d'), $readModel->getCheckOutDate());
        $this->assertFalse($readModel->isCancelled());
    }

    /** @test */
    public function it_updates_read_model_when_estimated_check_in_time_is_recorded(): void
    {
        // Given an existing booking
        $bookingId = BookingId::generate();
        $this->commandBus->handle(new CreateBooking(
            $bookingId,
            'Jane Smith',
            'Standard',
            new DateTimeImmutable('2023-06-10'),
            new DateTimeImmutable('2023-06-15')
        ));
        
        // When an estimated check-in time is recorded
        $estimatedTime = "14:00";
        $this->commandBus->handle(new RecordEstimatedCheckInTime($bookingId, $estimatedTime));
        
        // Then the read model should be updated
        $readModel = $this->readModelRepository->findById($bookingId->toString());
        
        $this->assertNotNull($readModel);
        $this->assertEquals($estimatedTime, $readModel->getEstimatedCheckInTime());
    }

    /** @test */
    public function it_updates_read_model_when_booking_is_cancelled(): void
    {
        // Given an existing booking
        $bookingId = BookingId::generate();
        $this->commandBus->handle(new CreateBooking(
            $bookingId,
            'Alice Brown',
            'Suite',
            new DateTimeImmutable('2023-07-20'),
            new DateTimeImmutable('2023-07-25')
        ));
        
        // When the booking is cancelled
        $cancellationReason = 'Change of plans';
        $this->commandBus->handle(new CancelBooking($bookingId, $cancellationReason));
        
        // Then the read model should reflect the cancellation
        $readModel = $this->readModelRepository->findById($bookingId->toString());
        
        $this->assertNotNull($readModel);
        $this->assertTrue($readModel->isCancelled());
        $this->assertEquals($cancellationReason, $readModel->getCancellationReason());
        $this->assertNotNull($readModel->getCancelledAt());
    }

    /** @test */
    public function it_can_find_active_bookings(): void
    {
        // Given multiple bookings, some cancelled
        $activeBooking1 = BookingId::generate();
        $activeBooking2 = BookingId::generate();
        $cancelledBooking = BookingId::generate();
        
        $this->commandBus->handle(new CreateBooking(
            $activeBooking1,
            'Active Guest 1',
            'Double',
            new DateTimeImmutable('2023-08-01'),
            new DateTimeImmutable('2023-08-05')
        ));
        
        $this->commandBus->handle(new CreateBooking(
            $activeBooking2,
            'Active Guest 2',
            'Single',
            new DateTimeImmutable('2023-08-10'),
            new DateTimeImmutable('2023-08-15')
        ));
        
        $this->commandBus->handle(new CreateBooking(
            $cancelledBooking,
            'Cancelled Guest',
            'Suite',
            new DateTimeImmutable('2023-09-01'),
            new DateTimeImmutable('2023-09-05')
        ));
        
        $this->commandBus->handle(new CancelBooking($cancelledBooking, 'No longer needed'));
        
        // When finding active bookings
        $activeBookings = $this->readModelRepository->findAllActive();
        
        // Then only non-cancelled bookings should be returned
        $this->assertCount(2, $activeBookings);
        
        $bookingIds = array_map(function ($booking) {
            return $booking->getId();
        }, $activeBookings);
        
        $this->assertContains($activeBooking1->toString(), $bookingIds);
        $this->assertContains($activeBooking2->toString(), $bookingIds);
        $this->assertNotContains($cancelledBooking->toString(), $bookingIds);
    }

    protected function tearDown(): void
    {
        $this->cleanTables();
    }
} 