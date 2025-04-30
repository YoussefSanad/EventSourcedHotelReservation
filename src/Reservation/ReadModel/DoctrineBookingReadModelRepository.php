<?php

namespace Reservation\ReadModel;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class DoctrineBookingReadModelRepository implements BookingReadModelRepository
{
    private Connection $connection;
    private string $tableName;

    public function __construct(Connection $connection, string $tableName = 'booking_read_model')
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function createTable(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        if ($schemaManager->tablesExist([$this->tableName])) {
            return;
        }

        $schema = new Schema();
        $table = $schema->createTable($this->tableName);
        $table->addColumn('id', 'string', ['length' => 36]);
        $table->addColumn('guest_name', 'string', ['length' => 255]);
        $table->addColumn('room_type', 'string', ['length' => 50]);
        $table->addColumn('check_in_date', 'string', ['length' => 20]);
        $table->addColumn('check_out_date', 'string', ['length' => 20]);
        $table->addColumn('estimated_check_in_time', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('is_cancelled', 'boolean', ['default' => false]);
        $table->addColumn('cancellation_reason', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cancelled_at', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('is_checked_in', 'boolean', ['default' => false]);
        $table->addColumn('actual_arrival_time', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('room_number', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('special_requests', 'text', ['notnull' => false]);
        $table->addColumn('checkin_time', 'string', ['notnull' => false, 'length' => 20]);
        $table->setPrimaryKey(['id']);

        $sqlQueries = $schema->toSql($this->connection->getDatabasePlatform());
        foreach ($sqlQueries as $sql) {
            $this->connection->executeStatement($sql);
        }
    }

    public function save(BookingReadModel $booking): void
    {
        $exists = $this->findById($booking->getId()) !== null;

        if ($exists) {
            $this->connection->update(
                $this->tableName,
                [
                    'guest_name' => $booking->getGuestName(),
                    'room_type' => $booking->getRoomType(),
                    'check_in_date' => $booking->getCheckInDate(),
                    'check_out_date' => $booking->getCheckOutDate(),
                    'estimated_check_in_time' => $booking->getEstimatedCheckInTime(),
                    'is_cancelled' => $booking->isCancelled() ? 1 : 0,
                    'cancellation_reason' => $booking->getCancellationReason(),
                    'cancelled_at' => $booking->getCancelledAt(),
                    'is_checked_in' => $booking->isCheckedIn() ? 1 : 0,
                    'actual_arrival_time' => $booking->getActualArrivalTime(),
                    'room_number' => $booking->getRoomNumber(),
                    'special_requests' => $booking->getSpecialRequests() ? json_encode($booking->getSpecialRequests()) : null,
                    'checkin_time' => $booking->getCheckinTime()
                ],
                ['id' => $booking->getId()]
            );
        } else {
            $this->connection->insert(
                $this->tableName,
                [
                    'id' => $booking->getId(),
                    'guest_name' => $booking->getGuestName(),
                    'room_type' => $booking->getRoomType(),
                    'check_in_date' => $booking->getCheckInDate(),
                    'check_out_date' => $booking->getCheckOutDate(),
                    'estimated_check_in_time' => $booking->getEstimatedCheckInTime(),
                    'is_cancelled' => $booking->isCancelled() ? 1 : 0,
                    'cancellation_reason' => $booking->getCancellationReason(),
                    'cancelled_at' => $booking->getCancelledAt(),
                    'is_checked_in' => $booking->isCheckedIn() ? 1 : 0,
                    'actual_arrival_time' => $booking->getActualArrivalTime(),
                    'room_number' => $booking->getRoomNumber(),
                    'special_requests' => $booking->getSpecialRequests() ? json_encode($booking->getSpecialRequests()) : null,
                    'checkin_time' => $booking->getCheckinTime()
                ]
            );
        }
    }

    public function findById(string $id): ?BookingReadModel
    {
        $data = $this->connection->fetchAssociative(
            "SELECT * FROM {$this->tableName} WHERE id = ?",
            [$id]
        );

        if (!$data) {
            return null;
        }

        return $this->hydrateBookingReadModel($data);
    }

    public function findAllActive(): array
    {
        $data = $this->connection->fetchAllAssociative(
            "SELECT * FROM {$this->tableName} WHERE is_cancelled = 0"
        );

        return array_map([$this, 'hydrateBookingReadModel'], $data);
    }

    public function findByGuestName(string $guestName): array
    {
        $data = $this->connection->fetchAllAssociative(
            "SELECT * FROM {$this->tableName} WHERE guest_name LIKE ?",
            ['%' . $guestName . '%']
        );

        return array_map([$this, 'hydrateBookingReadModel'], $data);
    }

    private function hydrateBookingReadModel(array $data): BookingReadModel
    {
        $specialRequests = null;
        if (!empty($data['special_requests'])) {
            $specialRequests = json_decode($data['special_requests'], true);
        }
        
        return new BookingReadModel(
            $data['id'],
            $data['guest_name'],
            $data['room_type'],
            $data['check_in_date'],
            $data['check_out_date'],
            $data['estimated_check_in_time'],
            (bool) $data['is_cancelled'],
            $data['cancellation_reason'],
            $data['cancelled_at'],
            (bool) $data['is_checked_in'],
            $data['actual_arrival_time'],
            $data['room_number'],
            $specialRequests,
            $data['checkin_time']
        );
    }
} 