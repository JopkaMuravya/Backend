<?php

namespace App\Repository\Interfaces;

use App\Entity\House;

interface HouseRepositoryInterface
{
    public function findById(int $id): ?House;
    public function findAll(): array;
    public function findAvailableHouses(): array;
    public function save(House $house): void;
    public function remove(House $house): void;
}