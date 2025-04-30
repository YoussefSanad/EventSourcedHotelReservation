<?php

require_once __DIR__ . '/../vendor/autoload.php';

$container = (require __DIR__ . '/../config/container.php')();
$connection = $container->get(Doctrine\DBAL\Connection::class);

// Drop existing tables
$connection->executeStatement('DROP TABLE IF EXISTS booking_read_model');
echo "Table booking_read_model dropped successfully.\n";

// Recreate the tables through the repository which will handle schema creation
$repository = $container->get(Reservation\ReadModel\BookingReadModelRepository::class);
echo "Table booking_read_model recreated successfully.\n"; 