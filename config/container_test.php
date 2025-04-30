<?php

use Common\CommandBus\CommandBus;
use Common\EventStore\DoctrineMessageRepositoryFactory;
use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Finance\BookingCheckinListener;
use Finance\Command\CreateInvoice;
use Finance\CommandHandler\CreateInvoiceHandler;
use Finance\InvoiceRepository;
use Finance\ReadModel\DoctrineInvoiceReadModelRepository;
use Finance\ReadModel\InvoiceProjector;
use Finance\ReadModel\InvoiceReadModelRepository;
use Finance\TransactionalInvoiceRepository;
use League\Tactician\CommandBus as TacticianCommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
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
use function DI\factory;
use function DI\get;

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
        
        // Bind MessageDispatcher interface to TransactionalMessageDispatcher implementation
        MessageDispatcher::class => get(TransactionalMessageDispatcher::class),
        
        // Reservation Read model repository
        BookingReadModelRepository::class => function (Connection $connection) {
            $repository = new DoctrineBookingReadModelRepository($connection);
            // Ensure table exists
            $repository->createTable();
            return $repository;
        },
        
        // Finance Read model repository
        InvoiceReadModelRepository::class => function (Connection $connection) {
            $repository = new DoctrineInvoiceReadModelRepository($connection);
            // Ensure table exists
            $repository->createTable();
            return $repository;
        },
        
        // Reservation Event projector
        BookingProjector::class => function (BookingReadModelRepository $repository) {
            return new BookingProjector($repository);
        },
        
        // Finance Event projector
        InvoiceProjector::class => function (InvoiceReadModelRepository $repository) {
            return new InvoiceProjector($repository);
        },
        
        // Command handlers - Reservation
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
        
        // Command handlers - Finance
        CreateInvoiceHandler::class => function (InvoiceRepository $repository) {
            return new CreateInvoiceHandler($repository);
        },
        
        // Command Bus configuration
        TacticianCommandBus::class => function (
            CreateBookingHandler $createBookingHandler,
            RecordEstimatedCheckInTimeHandler $recordEstimatedCheckInTimeHandler,
            CancelBookingHandler $cancelBookingHandler,
            InitiateCheckinHandler $initiateCheckinHandler,
            CreateInvoiceHandler $createInvoiceHandler
        ) {
            // Create handler locator and register all command handlers
            $locator = new InMemoryLocator();
            $locator->addHandler($createBookingHandler, CreateBooking::class);
            $locator->addHandler($recordEstimatedCheckInTimeHandler, RecordEstimatedCheckInTime::class);
            $locator->addHandler($cancelBookingHandler, CancelBooking::class);
            $locator->addHandler($initiateCheckinHandler, InitiateCheckin::class);
            $locator->addHandler($createInvoiceHandler, CreateInvoice::class);
            
            // Create command bus middleware
            $handlerMiddleware = new CommandHandlerMiddleware(
                new ClassNameExtractor(),
                $locator,
                new HandleInflector()
            );
            
            // Return the command bus with the middleware
            return new TacticianCommandBus([$handlerMiddleware]);
        },
        
        // Our command bus wrapper
        CommandBus::class => function (TacticianCommandBus $tacticianCommandBus) {
            return new CommandBus($tacticianCommandBus);
        },
        
        // Invoice repository
        InvoiceRepository::class => function (
            MessageRepository $messageRepository, 
            MessageDecorator $messageDecorator,
            TransactionalMessageDispatcher $dispatcher,
            InvoiceProjector $projector
        ) {
            // Add the projector as a consumer
            $dispatcher->addConsumer($projector);
            
            // Return the transactional repository
            return new TransactionalInvoiceRepository(
                $messageRepository,
                $messageDecorator,
                $dispatcher
            );
        },
    ]);
    
    // Build the container
    $container = $containerBuilder->build();
    
    // Manually set up the dispatcher
    $dispatcher = $container->get(TransactionalMessageDispatcher::class);
    
    // Set up the projector
    $projector = $container->get(BookingProjector::class);
    $dispatcher->addConsumer($projector);
    
    // Create the repository first (without the listener)
    $repository = new TransactionalBookingRepository(
        $container->get(MessageRepository::class),
        $container->get(MessageDecorator::class),
        $dispatcher
    );
    
    // Now create the listener with the repository
    $listener = new BookingCheckinListener($repository);
    
    // Set the command bus on the listener
    $listener->setCommandBus($container->get(CommandBus::class));
    
    // Add the listener to the dispatcher
    $dispatcher->addConsumer($listener);
    
    // Add the repository to the container
    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addDefinitions([
        BookingRepository::class => function() use ($repository) {
            return $repository;
        },
        BookingCheckinListener::class => function() use ($listener) {
            return $listener;
        }
    ]);
    
    // Merge the containers
    $newContainer = $containerBuilder->build();
    
    // Return the merged container
    foreach ($newContainer->getKnownEntryNames() as $name) {
        if (!$container->has($name)) {
            $container->set($name, $newContainer->get($name));
        }
    }
    
    return $container;
}; 