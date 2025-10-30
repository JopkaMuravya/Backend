<?php

namespace App\Repository\Interfaces;

use App\Entity\House;

interface HouseRepositoryInterface
{
    /** @return House[] */
    public function findAll(): array;

    /** @return House[] */
    public function findAvailable(): array;

    public function findById(int $id): ?House;
}