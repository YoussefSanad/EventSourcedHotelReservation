<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use EventSauce\MessageRepository\TableSchema\DefaultTableSchema;

// Load the main database configuration and modify for testing
$mainConfig = require __DIR__ . '/../config/database.php';
$testConfig = $mainConfig;
$testConfig['dbname'] = 'hotel_reservation_test';

// Connect to MySQL without database name
$connectionParams = $testConfig;
unset($connectionParams['dbname']);
$connection = DriverManager::getConnection($connectionParams);

// Create test database if it doesn't exist
$dbName = $testConfig['dbname'];
echo "Creating test database $dbName if it doesn't exist...\n";
$connection->executeStatement("DROP DATABASE IF EXISTS `$dbName`");
$connection->executeStatement("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Test database setup complete.\n";

// Connect to the test database
$connection = DriverManager::getConnection($testConfig);

// Create event store table
$tableName = 'event_store';
$tableSchema = new DefaultTableSchema();

echo "Creating event store table if it doesn't exist...\n";
$sql = <<<SQL
CREATE TABLE `{$tableName}` (
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
echo "Test setup complete.\n";

// Return the test config for use in other scripts
return $testConfig; 