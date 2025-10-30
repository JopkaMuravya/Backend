<?php

namespace App\Repository\Interfaces;

use App\Entity\Booking;

interface BookingRepositoryInterface
{
    /** @return Booking[] */
    public function findAll(): array;

    public function findById(int $id): ?Booking;

    public function save(Booking $booking): bool;

    public function delete(int $id): bool;

    /** @return Booking[] */
    public function findByHouseId(int $houseId): array;
}