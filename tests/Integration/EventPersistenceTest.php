<?php

namespace Tests\Integration;

use Common\CommandBus\CommandBus;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use League\Tactician\CommandBus as TacticianCommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use PHPUnit\Framework\TestCase;
use Reservation\BookingId;
use Reservation\BookingRepository;
use Reservation\Command\CreateBooking;
use Reservation\Command\RecordEstimatedCheckInTime;
use Reservation\CommandHandler\CreateBookingHandler;
use Reservation\CommandHandler\RecordEstimatedCheckInTimeHandler;

class EventPersistenceTest extends TestCase
{
    private Connection $connection;
    private BookingRepository $repository;
    private CommandBus $commandBus;
    private BookingId $bookingId;

    protected function setUp(): void
    {
        // Setup test database
        require_once __DIR__ . '/../../bin/setup_test_db.php';
        
        // Initialize the container with test configuration
        $container = (require __DIR__ . '/../../config/container_test.php')();
        
        // Get the connection and repository
        $this->connection = $container->get(Connection::class);
        $this->repository = $container->get(BookingRepository::class);
        
        // Set up command handlers
        $locator = new InMemoryLocator();
        $locator->addHandler(new CreateBookingHandler($this->repository), CreateBooking::class);
        $locator->addHandler(
            new RecordEstimatedCheckInTimeHandler($this->repository), 
            RecordEstimatedCheckInTime::class
        );
        
        // Create command bus
        $handlerMiddleware = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            $locator,
            new HandleInflector()
        );
        $tactician = new TacticianCommandBus([$handlerMiddleware]);
        $this->commandBus = new CommandBus($tactician);
        
        // Generate a test BookingId
        $this->bookingId = BookingId::generate();
    }
    
    public function testEventsArePersisted(): void
    {
        // Initial check - no events should exist
        $this->assertEventCount(0);
        
        // Create a booking
        $checkInDate = new DateTimeImmutable('2023-10-15');
        $checkOutDate = new DateTimeImmutable('2023-10-20');
        
        $createCommand = new CreateBooking(
            $this->bookingId,
            'Test Guest',
            'Test Room',
            $checkInDate,
            $checkOutDate
        );
        
        // Handle the command
        $this->commandBus->handle($createCommand);
        
        // After booking creation, we should have 1 event
        $this->assertEventCount(1);
        
        // Record estimated check-in time
        $estimatedTime = "14:00";
        $recordTimeCommand = new RecordEstimatedCheckInTime($this->bookingId, $estimatedTime);
        
        // Handle the command
        $this->commandBus->handle($recordTimeCommand);
        
        // After recording check-in time, we should have 2 events
        $this->assertEventCount(2);
        
        // Verify booking data is retrievable
        $booking = $this->repository->retrieveBooking($this->bookingId);
        
        $this->assertEquals('Test Guest', $booking->getGuestName());
        $this->assertEquals('Test Room', $booking->getRoomType());
        $this->assertEquals($checkInDate->format('Y-m-d'), $booking->getCheckInDate()->format('Y-m-d'));
        $this->assertEquals($checkOutDate->format('Y-m-d'), $booking->getCheckOutDate()->format('Y-m-d'));
        $this->assertEquals($estimatedTime, $booking->getEstimatedCheckInTime());
    }
    
    private function assertEventCount(int $expectedCount): void
    {
        $count = $this->connection->fetchOne('SELECT COUNT(*) FROM event_store');
        $this->assertSame($expectedCount, (int)$count);
    }
    
    protected function tearDown(): void
    {
        // Clean up by truncating the event store table
        $this->connection->executeStatement('TRUNCATE TABLE event_store');
    }
} 