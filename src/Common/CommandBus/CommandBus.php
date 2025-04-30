<?php

namespace Common\CommandBus;

use League\Tactician\CommandBus as TacticianCommandBus;

class CommandBus
{
    private TacticianCommandBus $commandBus;

    public function __construct(TacticianCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function handle(object $command): void
    {
        $this->commandBus->handle($command);
    }
} 