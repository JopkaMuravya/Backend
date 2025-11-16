<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Booking;
use App\Entity\House;
use App\Entity\User;
use App\Repository\Interfaces\BookingRepositoryInterface;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
    ) {
    }

    public function createBooking(User $user, House $house, string $comment): Booking
    {
        if (!$house->isAvailable()) {
            throw new InvalidArgumentException('House is not available');
        }

        $booking = new Booking($user, $house, $comment, 'pending');
        $this->bookingRepository->save($booking);

        return $booking;
    }

    public function confirmBooking(int $bookingId): void
    {
        $booking = $this->bookingRepository->findById($bookingId);

        if (!$booking) {
            throw new InvalidArgumentException('Booking not found');
        }

        $booking->setStatus('confirmed');
        $this->bookingRepository->save($booking);
    }

    public function cancelBooking(int $bookingId, User $user): void
    {
        $booking = $this->bookingRepository->findById($bookingId);

        if (!$booking) {
            throw new InvalidArgumentException('Booking not found');
        }

        if ($booking->getGuest()->getId() !== $user->getId()) {
            throw new InvalidArgumentException('You can only cancel your own bookings');
        }

        if (!in_array($booking->getStatus(), ['pending', 'confirmed'])) {
            throw new InvalidArgumentException('This booking cannot be cancelled');
        }

        $booking->setStatus('cancelled');
        $this->bookingRepository->save($booking);
    }

    public function getUserBookings(User $user): array
    {
        return $this->bookingRepository->findByUser($user);
    }

    public function getPendingBookings(): array
    {
        return $this->bookingRepository->findByPending();
    }

    public function getHouseBookings(House $house): array
    {
        return $this->bookingRepository->findByHouse($house);
    }
}
