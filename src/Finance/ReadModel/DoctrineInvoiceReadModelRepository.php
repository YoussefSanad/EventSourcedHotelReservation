<?php

namespace Finance\ReadModel;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class DoctrineInvoiceReadModelRepository implements InvoiceReadModelRepository
{
    private Connection $connection;
    private string $tableName;

    public function __construct(Connection $connection, string $tableName = 'invoice_read_model')
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
        $table->addColumn('booking_id', 'string', ['length' => 36]);
        $table->addColumn('guest_name', 'string', ['length' => 255]);
        $table->addColumn('room_type', 'string', ['length' => 50]);
        $table->addColumn('room_number', 'string', ['length' => 20]);
        $table->addColumn('check_in_date', 'string', ['length' => 20]);
        $table->addColumn('check_out_date', 'string', ['length' => 20]);
        $table->addColumn('total_amount', 'float');
        $table->addColumn('status', 'string', ['length' => 20]);
        $table->addColumn('paid_at', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('created_at', 'string', ['length' => 20]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['booking_id'], 'idx_invoice_booking_id');
        $table->addIndex(['guest_name'], 'idx_invoice_guest_name');
        $table->addIndex(['status'], 'idx_invoice_status');

        $sqlQueries = $schema->toSql($this->connection->getDatabasePlatform());
        foreach ($sqlQueries as $sql) {
            $this->connection->executeStatement($sql);
        }
    }

    public function save(InvoiceReadModel $invoice): void
    {
        $exists = $this->findById($invoice->getId()) !== null;

        if ($exists) {
            $this->connection->update(
                $this->tableName,
                [
                    'booking_id' => $invoice->getBookingId(),
                    'guest_name' => $invoice->getGuestName(),
                    'room_type' => $invoice->getRoomType(),
                    'room_number' => $invoice->getRoomNumber(),
                    'check_in_date' => $invoice->getCheckInDate(),
                    'check_out_date' => $invoice->getCheckOutDate(),
                    'total_amount' => $invoice->getTotalAmount(),
                    'status' => $invoice->getStatus(),
                    'paid_at' => $invoice->getPaidAt(),
                    'created_at' => $invoice->getCreatedAt()
                ],
                ['id' => $invoice->getId()]
            );
        } else {
            $this->connection->insert(
                $this->tableName,
                [
                    'id' => $invoice->getId(),
                    'booking_id' => $invoice->getBookingId(),
                    'guest_name' => $invoice->getGuestName(),
                    'room_type' => $invoice->getRoomType(),
                    'room_number' => $invoice->getRoomNumber(),
                    'check_in_date' => $invoice->getCheckInDate(),
                    'check_out_date' => $invoice->getCheckOutDate(),
                    'total_amount' => $invoice->getTotalAmount(),
                    'status' => $invoice->getStatus(),
                    'paid_at' => $invoice->getPaidAt(),
                    'created_at' => $invoice->getCreatedAt()
                ]
            );
        }
    }

    public function findById(string $id): ?InvoiceReadModel
    {
        $data = $this->connection->fetchAssociative(
            "SELECT * FROM {$this->tableName} WHERE id = ?",
            [$id]
        );

        if (!$data) {
            return null;
        }

        return $this->hydrateInvoiceReadModel($data);
    }

    public function findByBookingId(string $bookingId): array
    {
        $data = $this->connection->fetchAllAssociative(
            "SELECT * FROM {$this->tableName} WHERE booking_id = ?",
            [$bookingId]
        );

        return array_map([$this, 'hydrateInvoiceReadModel'], $data);
    }

    public function findAllPending(): array
    {
        $data = $this->connection->fetchAllAssociative(
            "SELECT * FROM {$this->tableName} WHERE status = 'pending'"
        );

        return array_map([$this, 'hydrateInvoiceReadModel'], $data);
    }

    public function findByGuestName(string $guestName): array
    {
        $data = $this->connection->fetchAllAssociative(
            "SELECT * FROM {$this->tableName} WHERE guest_name LIKE ?",
            ['%' . $guestName . '%']
        );

        return array_map([$this, 'hydrateInvoiceReadModel'], $data);
    }

    private function hydrateInvoiceReadModel(array $data): InvoiceReadModel
    {
        return new InvoiceReadModel(
            $data['id'],
            $data['booking_id'],
            $data['guest_name'],
            $data['room_type'],
            $data['room_number'],
            $data['check_in_date'],
            $data['check_out_date'],
            (float) $data['total_amount'],
            $data['status'],
            $data['paid_at'],
            $data['created_at']
        );
    }
} 