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
use Reservation\Command\CancelBooking;
use Reservation\Command\CreateBooking;
use Reservation\Command\InitiateCheckin;
use Reservation\Command\RecordEstimatedCheckInTime;
use Reservation\CommandHandler\CancelBookingHandler;
use Reservation\CommandHandler\CreateBookingHandler;
use Reservation\CommandHandler\InitiateCheckinHandler;
use Reservation\CommandHandler\RecordEstimatedCheckInTimeHandler;

// Initialize the container
$container = (require __DIR__ . '/../config/container.php')();

// Get the repository
$repository = $container->get(BookingRepository::class);

// Set up command handlers
$locator = new InMemoryLocator();
$locator->addHandler(new CreateBookingHandler($repository), CreateBooking::class);
$locator->addHandler(new RecordEstimatedCheckInTimeHandler($repository), RecordEstimatedCheckInTime::class);
$locator->addHandler(new CancelBookingHandler($repository), CancelBooking::class);
$locator->addHandler(new InitiateCheckinHandler($repository), InitiateCheckin::class);

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
echo "- Is Cancelled: " . ($booking->isCancelled() ? 'Yes' : 'No') . "\n";
echo "- Is Checked In: " . ($booking->isCheckedIn() ? 'Yes' : 'No') . "\n\n";

// Initiate check-in
$actualArrivalTime = "16:15";
$roomNumber = "301";
$specialRequests = ["Extra pillows", "Late checkout request"];

$initiateCheckinCommand = new InitiateCheckin(
    $bookingId, 
    $actualArrivalTime,
    $roomNumber,
    $specialRequests
);

echo "Initiating check-in process...\n";
$commandBus->handle($initiateCheckinCommand);
echo "Check-in initiated successfully.\n";

// Retrieve the booking again to verify check-in
echo "Retrieving booking from event store after check-in...\n";
$booking = $repository->retrieveBooking($bookingId);

echo "Updated booking details after check-in:\n";
echo "- Guest: " . $booking->getGuestName() . "\n";
echo "- Room Type: " . $booking->getRoomType() . "\n";
echo "- Check-in Date: " . $booking->getCheckInDate()->format('Y-m-d') . "\n";
echo "- Check-out Date: " . $booking->getCheckOutDate()->format('Y-m-d') . "\n";
echo "- Estimated Check-in Time: " . $booking->getEstimatedCheckInTime() . "\n";
echo "- Is Checked In: " . ($booking->isCheckedIn() ? 'Yes' : 'No') . "\n";
echo "- Actual Arrival Time: " . $booking->getActualArrivalTime() . "\n";
echo "- Room Number: " . $booking->getRoomNumber() . "\n";
echo "- Special Requests: " . implode(", ", $booking->getSpecialRequests() ?? []) . "\n";
echo "- Check-in Time: " . $booking->getCheckinTime()->format('Y-m-d H:i:s') . "\n\n";

// Try to check in an already checked-in booking (should throw an exception)
try {
    echo "Attempting to check in an already checked-in booking...\n";
    $commandBus->handle(new InitiateCheckin($bookingId, "17:00"));
    echo "This should not be reached.\n";
} catch (\DomainException $e) {
    echo "Exception caught as expected: " . $e->getMessage() . "\n\n";
}

// Try to cancel the booking
$cancellationReason = "Guest plans changed";
$cancelCommand = new CancelBooking($bookingId, $cancellationReason);

echo "Cancelling booking with reason: $cancellationReason...\n";
try {
    $commandBus->handle($cancelCommand);
    echo "Booking cancelled successfully.\n";
} catch (\DomainException $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
} 