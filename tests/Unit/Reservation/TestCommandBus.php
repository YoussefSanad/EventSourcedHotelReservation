<?php

namespace Tests\Unit\Reservation;

use Common\CommandBus\CommandBus;
use DI\Container;
use EventSauce\EventSourcing\AggregateRootRepository;
use League\Tactician\CommandBus as TacticianCommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use Reservation\BookingRepository;
use Reservation\Command\CancelBooking;
use Reservation\Command\CreateBooking;
use Reservation\Command\RecordEstimatedCheckInTime;
use Reservation\CommandHandler\CancelBookingHandler;
use Reservation\CommandHandler\CreateBookingHandler;
use Reservation\CommandHandler\RecordEstimatedCheckInTimeHandler;

class TestCommandBus
{
    private CommandBus $commandBus;

    public function __construct(Container $container)
    {
        $locator = new InMemoryLocator();
        
        // Get the handlers from the container
        $locator->addHandler($container->get(CreateBookingHandler::class), CreateBooking::class);
        $locator->addHandler($container->get(RecordEstimatedCheckInTimeHandler::class), RecordEstimatedCheckInTime::class);
        $locator->addHandler($container->get(CancelBookingHandler::class), CancelBooking::class);
        
        // Create the command bus
        $handlerMiddleware = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            $locator,
            new HandleInflector()
        );
        
        $tacticianCommandBus = new TacticianCommandBus([$handlerMiddleware]);
        $this->commandBus = new CommandBus($tacticianCommandBus);
    }

    public function handle(object $command): void
    {
        $this->commandBus->handle($command);
    }

    public static function create(BookingRepository $repository): CommandBus
    {
        // Create handler locator
        $locator = new InMemoryLocator();
        
        // Register command handlers
        $locator->addHandler(new CreateBookingHandler($repository), CreateBooking::class);
        $locator->addHandler(new RecordEstimatedCheckInTimeHandler($repository), RecordEstimatedCheckInTime::class);
        $locator->addHandler(new CancelBookingHandler($repository), CancelBooking::class);
        
        // Create the command bus with appropriate middleware
        $handlerMiddleware = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            $locator,
            new HandleInflector()
        );
        
        $tacticianCommandBus = new TacticianCommandBus([$handlerMiddleware]);
        
        return new CommandBus($tacticianCommandBus);
    }
} 