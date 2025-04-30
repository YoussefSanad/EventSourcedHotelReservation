<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Common\CommandBus\CommandBus;
use Finance\Command\CreateInvoice;
use Finance\CommandHandler\CreateInvoiceHandler;
use Finance\InvoiceId;
use Finance\InvoiceRepository;
use League\Tactician\CommandBus as TacticianCommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use Reservation\Event\CheckinInitiated;

// Initialize the container
$container = (require __DIR__ . '/../config/container.php')();

// Get the repositories
$invoiceRepository = $container->get(InvoiceRepository::class);

// Set up command handlers
$locator = new InMemoryLocator();
$locator->addHandler(
    new CreateInvoiceHandler($invoiceRepository), 
    CreateInvoice::class
);

// Create command bus
$handlerMiddleware = new CommandHandlerMiddleware(
    new ClassNameExtractor(),
    $locator,
    new HandleInflector()
);
$tactician = new TacticianCommandBus([$handlerMiddleware]);
$commandBus = new CommandBus($tactician);

// Generate a new InvoiceId
$invoiceId = InvoiceId::generate();
echo "Generated invoice ID: " . $invoiceId->toString() . "\n";

// Create test data
$bookingId = 'test-booking-' . uniqid();
$guestName = 'Jane Doe';
$roomType = 'Deluxe Suite';
$roomNumber = '505';
$checkInDate = new \DateTimeImmutable('2023-11-15');
$checkOutDate = new \DateTimeImmutable('2023-11-20');
$roomRate = 300.00; // $300 per night

// Create an invoice
$createInvoiceCommand = new CreateInvoice(
    $invoiceId,
    $bookingId,
    $guestName,
    $roomType,
    $roomNumber,
    $checkInDate,
    $checkOutDate,
    $roomRate
);

echo "Creating invoice...\n";
$commandBus->handle($createInvoiceCommand);
echo "Invoice created successfully.\n";

// Retrieve the invoice to verify
echo "Retrieving invoice from event store...\n";
$invoice = $invoiceRepository->retrieveInvoice($invoiceId);

echo "Invoice details:\n";
echo "- ID: " . $invoiceId->toString() . "\n";
echo "- Booking ID: " . $invoice->getBookingId() . "\n";
echo "- Guest: " . $invoice->getGuestName() . "\n";
echo "- Room Type: " . $invoice->getRoomType() . "\n";
echo "- Room Number: " . $invoice->getRoomNumber() . "\n";
echo "- Check-in Date: " . $invoice->getCheckInDate()->format('Y-m-d') . "\n";
echo "- Check-out Date: " . $invoice->getCheckOutDate()->format('Y-m-d') . "\n";
echo "- Total Amount: $" . number_format($invoice->getTotalAmount(), 2) . "\n";
echo "- Status: " . $invoice->getStatus() . "\n";

// Test the integrated flow
echo "\nTesting automatic invoice creation when a booking is checked in...\n";
echo "When a guest checks in, the BookingCheckinListener will create an invoice automatically.\n";
echo "To see this in action, run the test_event_store.php script and check the invoice_read_model table.\n"; 