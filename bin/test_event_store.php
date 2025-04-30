<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Common\CommandBus\CommandBus;
use League\Tactician\CommandBus as TacticianCommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use Reservation\BookingId;
use Reservation\BookingRepository;
use Reservation\Command\CreateBooking;
use Reservation\Command\RecordEstimatedCheckInTime;
use Reservation\CommandHandler\CreateBookingHandler;
use Reservation\CommandHandler\RecordEstimatedCheckInTimeHandler;

// Initialize the container
$container = (require __DIR__ . '/../config/container.php')();

// Get the repository
$repository = $container->get(BookingRepository::class);

// Set up command handlers
$locator = new InMemoryLocator();
$locator->addHandler(new CreateBookingHandler($repository), CreateBooking::class);
$locator->addHandler(new RecordEstimatedCheckInTimeHandler($repository), RecordEstimatedCheckInTime::class);

// Create command bus
$handlerMiddleware = new CommandHandlerMiddleware(
    new ClassNameExtractor(),
    $locator,
    new HandleInflector()
);
$tactician = new TacticianCommandBus([$handlerMiddleware]);
$commandBus = new CommandBus($tactician);

// Generate a new BookingId
$bookingId = BookingId::generate();
echo "Generated booking ID: " . $bookingId->toString() . "\n";

// Create a booking
$checkInDate = new DateTimeImmutable('2023-10-15');
$checkOutDate = new DateTimeImmutable('2023-10-20');

$createCommand = new CreateBooking(
    $bookingId,
    'John Smith',
    'Deluxe Suite',
    $checkInDate,
    $checkOutDate
);

echo "Creating booking...\n";
$commandBus->handle($createCommand);
echo "Booking created successfully.\n";

// Record estimated check-in time
$estimatedTime = "15:30";
$recordTimeCommand = new RecordEstimatedCheckInTime($bookingId, $estimatedTime);

echo "Recording estimated check-in time as $estimatedTime...\n";
$commandBus->handle($recordTimeCommand);
echo "Estimated check-in time recorded successfully.\n";

// Retrieve the booking to verify
echo "Retrieving booking from event store...\n";
$booking = $repository->retrieveBooking($bookingId);

echo "Booking details:\n";
echo "- Guest: " . $booking->getGuestName() . "\n";
echo "- Room Type: " . $booking->getRoomType() . "\n";
echo "- Check-in Date: " . $booking->getCheckInDate()->format('Y-m-d') . "\n";
echo "- Check-out Date: " . $booking->getCheckOutDate()->format('Y-m-d') . "\n";
echo "- Estimated Check-in Time: " . $booking->getEstimatedCheckInTime() . "\n"; 