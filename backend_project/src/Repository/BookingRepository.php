<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Repository\Interfaces\BookingRepositoryInterface;
use App\Services\CsvDataService;

class BookingRepository implements BookingRepositoryInterface
{
    public function __construct(private CsvDataService $csvService) {}

    public function findAll(): array
    {
        $data = $this->csvService->readBookings();
        return array_map([Booking::class, 'fromArray'], $data);
    }

    public function findById(int $id): ?Booking
    {
        $bookings = $this->findAll();
        foreach ($bookings as $booking) {
            if ($booking->getId() === $id) {
                return $booking;
            }
        }
        return null;
    }

    public function save(Booking $booking): bool
    {
        $bookings = $this->findAll();

        if ($booking->getId() === 0) {
            $newId = empty($bookings) ? 1 : max(array_map(fn($b) => $b->getId(), $bookings)) + 1;
            $reflection = new \ReflectionClass($booking);
            $idProperty = $reflection->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($booking, $newId);

            $bookings[] = $booking;
        } else {
            foreach ($bookings as $key => $existingBooking) {
                if ($existingBooking->getId() === $booking->getId()) {
                    $bookings[$key] = $booking;
                    break;
                }
            }
        }
        return $this->csvService->writeBookings(
            array_map(fn($b) => $b->toArray(), $bookings)
        );
    }

    public function delete(int $id): bool
    {
        $bookings = $this->findAll();
        $bookings = array_filter($bookings, fn($booking) => $booking->getId() !== $id);
        
        return $this->csvService->writeBookings(
            array_map(fn($b) => $b->toArray(), array_values($bookings))
        );
    }

    public function findByHouseId(int $houseId): array
    {
        $bookings = $this->findAll();
        return array_filter($bookings, fn($booking) => $booking->getHouseId() === $houseId);
    }
}