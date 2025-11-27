<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\HouseRepository")]
#[ORM\Table(name: 'houses')]
class House
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $pricePerNight;

    #[ORM\Column(type: 'integer')]
    private int $capacity;

    #[ORM\Column(type: 'integer')]
    private int $distanceToSea;

    #[ORM\Column(type: 'string', length: 255)]
    private string $amenities;

    #[ORM\Column(type: 'boolean')]
    private bool $isAvailable;

    public function __construct(
        string $name,
        int $pricePerNight,
        int $capacity,
        int $distanceToSea,
        string $amenities,
        bool $isAvailable = true,
    ) {
        $this->name = $name;
        $this->pricePerNight = $pricePerNight;
        $this->capacity = $capacity;
        $this->distanceToSea = $distanceToSea;
        $this->amenities = $amenities;
        $this->isAvailable = $isAvailable;
    }

    // Геттеры
    public function getId(): int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getPricePerNight(): int
    {
        return $this->pricePerNight;
    }
    public function getCapacity(): int
    {
        return $this->capacity;
    }
    public function getDistanceToSea(): int
    {
        return $this->distanceToSea;
    }
    public function getAmenities(): string
    {
        return $this->amenities;
    }
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    // Бизнес-логика
    public function canAccommodate(int $guests): bool
    {
        return $this->capacity >= $guests;
    }

    public function calculateTotalPrice(int $nights): int
    {
        return $this->pricePerNight * $nights;
    }

    public function hasAmenity(string $amenity): bool
    {
        return str_contains($this->amenities, $amenity);
    }
}
