<?php

namespace Common\EventStore;

use Doctrine\DBAL\Connection;
use EventSauce\MessageRepository\DoctrineMessageRepository\DoctrineMessageRepository;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\MessageRepository\TableSchema\DefaultTableSchema;

class DoctrineMessageRepositoryFactory
{
    public static function create(
        Connection $connection,
        MessageSerializer $serializer,
        string $tableName = 'event_store'
    ): MessageRepository {
        return new DoctrineMessageRepository(
            $connection,
            $tableName,
            $serializer,
            0, // JSON encode options
            new DefaultTableSchema()
        );
    }
} 