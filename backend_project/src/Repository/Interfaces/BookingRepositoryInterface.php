<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use App\Entity\Booking;
use App\Entity\House;
use App\Entity\User;

interface BookingRepositoryInterface
{
    public function findById(int $id): ?Booking;
    public function findAll(): array;
    public function findByUser(User $user): array;
    public function findByPending(): array;
    public function findByHouse(House $house): array;
    public function save(Booking $booking): void;
    public function remove(Booking $booking): void;
}
