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
use Reservation\Command\CancelBooking;
use Reservation\Command\CreateBooking;
use Reservation\Command\InitiateCheckin;
use Reservation\Command\RecordEstimatedCheckInTime;
use Reservation\CommandHandler\CancelBookingHandler;
use Reservation\CommandHandler\CreateBookingHandler;
use Reservation\CommandHandler\InitiateCheckinHandler;
use Reservation\CommandHandler\RecordEstimatedCheckInTimeHandler;
use Reservation\Event\BookingCancelled;
use Reservation\Event\BookingCreated;
use Reservation\Event\CheckinInitiated;
use Reservation\Event\EstimatedCheckInTimeRecorded;
use Reservation\ReadModel\BookingProjector;
use Reservation\ReadModel\BookingReadModelRepository;
use Reservation\ReadModel\DoctrineBookingReadModelRepository;
use Reservation\TransactionalBookingRepository;
use Reservation\TransactionalMessageDispatcher;

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

        // Transactional message dispatcher
        TransactionalMessageDispatcher::class => function (Connection $connection) {
            return new TransactionalMessageDispatcher($connection);
        },
        
        // Read model repository
        BookingReadModelRepository::class => function (Connection $connection) {
            $repository = new DoctrineBookingReadModelRepository($connection);
            // Ensure table exists
            $repository->createTable();
            return $repository;
        },
        
        // Event projector
        BookingProjector::class => function (BookingReadModelRepository $repository) {
            return new BookingProjector($repository);
        },
        
        // Booking repository
        BookingRepository::class => function (
            MessageRepository $messageRepository, 
            MessageDecorator $messageDecorator,
            TransactionalMessageDispatcher $dispatcher,
            BookingProjector $projector
        ) {
            // Add the projector as a consumer
            $dispatcher->addConsumer($projector);
            
            // Return the transactional repository
            return new TransactionalBookingRepository(
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

        CancelBookingHandler::class => function (BookingRepository $repository) {
            return new CancelBookingHandler($repository);
        },
        
        InitiateCheckinHandler::class => function (BookingRepository $repository) {
            return new InitiateCheckinHandler($repository);
        },
    ]);
    
    return $containerBuilder->build();
}; 