<?php

use Common\EventStore\DoctrineMessageRepositoryFactory;
use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Reservation\Booking;
use Reservation\BookingId;
use Reservation\BookingRepository;
use Reservation\Command\CreateBooking;
use Reservation\Command\RecordEstimatedCheckInTime;
use Reservation\CommandHandler\CreateBookingHandler;
use Reservation\CommandHandler\RecordEstimatedCheckInTimeHandler;
use Reservation\Event\BookingCreated;
use Reservation\Event\EstimatedCheckInTimeRecorded;

return function () {
    $containerBuilder = new ContainerBuilder();
    
    // Load test database configuration
    $dbConfig = require __DIR__ . '/database_test.php';
    
    // Configure service definitions
    $containerBuilder->addDefinitions([
        // Database connection
        Connection::class => function () use ($dbConfig) {
            return DriverManager::getConnection($dbConfig);
        },
        
        // Message serializer
        MessageSerializer::class => function () {
            return new ConstructingMessageSerializer();
        },
        
        // Message decorator
        MessageDecorator::class => function () {
            return new DefaultHeadersDecorator();
        },
        
        // Message repository for event storage
        MessageRepository::class => function (Connection $connection, MessageSerializer $serializer) {
            return DoctrineMessageRepositoryFactory::create(
                $connection,
                $serializer,
                'event_store'
            );
        },
        
        // Booking repository
        BookingRepository::class => function (MessageRepository $messageRepository, MessageDecorator $messageDecorator) {
            $dispatcher = new SynchronousMessageDispatcher();
            
            return new BookingRepository(
                $messageRepository,
                $messageDecorator,
                $dispatcher
            );
        },
        
        // Command handlers
        CreateBookingHandler::class => function (BookingRepository $repository) {
            return new CreateBookingHandler($repository);
        },
        
        RecordEstimatedCheckInTimeHandler::class => function (BookingRepository $repository) {
            return new RecordEstimatedCheckInTimeHandler($repository);
        },
    ]);
    
    return $containerBuilder->build();
}; 