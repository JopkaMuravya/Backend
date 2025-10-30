<?php

namespace App\Repository;

use App\Entity\House;
use App\Repository\Interfaces\HouseRepositoryInterface;
use App\Services\CsvDataService;

class HouseRepository implements HouseRepositoryInterface
{
    public function __construct(private CsvDataService $csvService) {}

    public function findAll(): array
    {
        $data = $this->csvService->readHouses();
        return array_map([House::class, 'fromArray'], $data);
    }

    public function findAvailable(): array
    {
        $houses = $this->findAll();
        return array_filter($houses, fn($house) => $house->isAvailable());
    }

    public function findById(int $id): ?House
    {
        $houses = $this->findAll();
        foreach ($houses as $house) {
            if ($house->getId() === $id) {
                return $house;
            }
        }
        return null;
    }
}