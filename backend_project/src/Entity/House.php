<?php

namespace App\Entity;

class House
{
    public function __construct(
        private int $id,
        private string $name,
        private int $pricePerNight,
        private int $capacity,
        private int $distanceToSea,
        private string $amenities,
        private bool $isAvailable
    ) {}

    // Геттеры
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getPricePerNight(): int { return $this->pricePerNight; }
    public function getCapacity(): int { return $this->capacity; }
    public function getDistanceToSea(): int { return $this->distanceToSea; }
    public function getAmenities(): string { return $this->amenities; }
    public function isAvailable(): bool { return $this->isAvailable; }

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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price_per_night' => $this->pricePerNight,
            'capacity' => $this->capacity,
            'distance_to_sea' => $this->distanceToSea,
            'amenities' => $this->amenities,
            'is_available' => $this->isAvailable
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            $data['name'],
            (int)$data['price_per_night'],
            (int)$data['capacity'],
            (int)$data['distance_to_sea'],
            $data['amenities'],
            $data['is_available'] === 'true'
        );
    }
}