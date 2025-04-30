<?php

namespace Tests\Unit\Reservation;

use Common\CommandBus\CommandBus;
use Reservation\Booking;
use Reservation\BookingId;
use Reservation\BookingRepository;
use EventSauce\EventSourcing\TestUtilities\AggregateRootTestCase;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;

abstract class BookingTestCase extends AggregateRootTestCase
{
    protected CommandBus $commandBus;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBus = TestCommandBus::create($this->repository);
    }
    
    protected function newAggregateRootId(): AggregateRootId
    {
        return BookingId::generate();
    }
    
    protected function aggregateRootClassName(): string
    {
        return Booking::class;
    }
    
    protected function aggregateRootRepository(
        string $className,
        MessageRepository $repository,
        MessageDispatcher $dispatcher,
        MessageDecorator $decorator
    ): AggregateRootRepository {
        return new BookingRepository($repository, $decorator, $dispatcher);
    }
    
    public function handle(object $command): void
    {
        $this->commandBus->handle($command);
    }
} 