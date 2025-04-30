<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use EventSauce\MessageRepository\TableSchema\DefaultTableSchema;

$config = require __DIR__ . '/../config/database.php';

// Connect to MySQL without database name
$connectionParams = $config;
unset($connectionParams['dbname']);
$connection = DriverManager::getConnection($connectionParams);

// Create database if it doesn't exist
$dbName = $config['dbname'];
echo "Creating database $dbName if it doesn't exist...\n";
$connection->executeStatement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Database setup complete.\n";

// Connect to the database
$connection = DriverManager::getConnection($config);

// Create event store table
$tableName = 'event_store';
$tableSchema = new DefaultTableSchema();

echo "Creating event store table if it doesn't exist...\n";
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$tableName}` (
    `{$tableSchema->incrementalIdColumn()}` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
    `{$tableSchema->eventIdColumn()}` BINARY(16) NOT NULL,
    `{$tableSchema->aggregateRootIdColumn()}` BINARY(16) NOT NULL,
    `{$tableSchema->versionColumn()}` INT NOT NULL,
    `{$tableSchema->payloadColumn()}` JSON NOT NULL,
    PRIMARY KEY (`{$tableSchema->incrementalIdColumn()}`),
    INDEX `{$tableName}_aggregate_root_id` (`{$tableSchema->aggregateRootIdColumn()}`),
    INDEX `{$tableName}_event_id` (`{$tableSchema->eventIdColumn()}`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

$connection->executeStatement($sql);
echo "Event store table created successfully.\n";
echo "Setup complete.\n"; 