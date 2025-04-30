<?php

namespace Reservation\ReadModel;

interface BookingReadModelRepository
{
    public function save(BookingReadModel $booking): void;
    
    public function findById(string $id): ?BookingReadModel;
    
    public function findAllActive(): array;
    
    public function findByGuestName(string $guestName): array;
} 