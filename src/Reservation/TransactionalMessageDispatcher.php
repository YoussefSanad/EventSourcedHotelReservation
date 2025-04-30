<?php

namespace Reservation;

use Doctrine\DBAL\Connection;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use Throwable;

/**
 * This class ensures that messages are dispatched to consumers in the same transaction
 * as event storage, ensuring strong consistency for read models
 */
class TransactionalMessageDispatcher implements MessageDispatcher
{
    private Connection $connection;
    private array $consumers = [];
    private array $queuedMessages = [];
    private bool $isDispatching = false;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function addConsumer(object $consumer): void
    {
        $this->consumers[] = $consumer;
    }

    public function dispatch(Message ...$messages): void
    {
        // If we're already dispatching, don't re-dispatch
        if ($this->isDispatching) {
            return;
        }

        // Queue messages for dispatch
        $this->queuedMessages = array_merge($this->queuedMessages, $messages);

        // If transaction is active, return and let the commit handle dispatching
        if ($this->connection->isTransactionActive()) {
            return;
        }

        $this->dispatchQueuedMessages();
    }

    public function dispatchQueuedMessages(): void
    {
        if (empty($this->queuedMessages) || $this->isDispatching) {
            return;
        }

        $messagesToDispatch = $this->queuedMessages;
        $this->queuedMessages = [];
        $this->isDispatching = true;

        try {
            $this->dispatchMessagesToConsumers($messagesToDispatch);
        } finally {
            $this->isDispatching = false;
        }
    }

    /**
     * Begins a transaction for message handling
     */
    public function beginTransaction(): void
    {
        if (!$this->connection->isTransactionActive()) {
            $this->connection->beginTransaction();
        }
    }

    /**
     * Commits the transaction and dispatches queued messages
     */
    public function commitTransaction(): void
    {
        if (!$this->connection->isTransactionActive()) {
            return;
        }

        try {
            $this->connection->commit();
            $this->dispatchQueuedMessages();
        } catch (Throwable $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Rollbacks the transaction and clears queued messages
     */
    public function rollbackTransaction(): void
    {
        if (!$this->connection->isTransactionActive()) {
            return;
        }

        $this->connection->rollBack();
        $this->queuedMessages = [];
    }

    private function dispatchMessagesToConsumers(array $messages): void
    {
        foreach ($messages as $message) {
            foreach ($this->consumers as $consumer) {
                if (method_exists($consumer, 'handleMessage')) {
                    $consumer->handleMessage($message);
                }
            }
        }
    }
} 