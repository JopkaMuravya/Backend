<?php

namespace App\Tests\Unit\Services;

use App\Entity\Booking;
use App\Entity\User;
use App\Entity\House;
use App\Repository\Interfaces\BookingRepositoryInterface;
use App\Services\BookingService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BookingServiceTest extends TestCase
{
    private BookingRepositoryInterface $bookingRepository;
    private BookingService $bookingService;
    private User $user;
    private User $otherUser;
    private House $availableHouse;
    private House $unavailableHouse;

    protected function setUp(): void
    {
        $this->bookingRepository = $this->createMock(BookingRepositoryInterface::class);
        $this->bookingService = new BookingService($this->bookingRepository);
        
        $this->user = $this->createUser(1, 'test@example.com', '+1234567890', 'John', 'Doe');
        $this->otherUser = $this->createUser(2, 'other@example.com', '+0987654321', 'Jane', 'Smith');
        
        $this->availableHouse = $this->createHouse(1, 'Test House', 100, 4, 500, 'WiFi,TV', true);
        $this->unavailableHouse = $this->createHouse(2, 'Unavailable House', 150, 6, 300, 'WiFi,TV,Pool', false);
    }

    private function createUser(int $id, string $email, string $phone, string $firstName, string $lastName): User
    {
        $user = new User($email, $phone, 'password', $firstName, $lastName);
        
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, $id);
        
        return $user;
    }

    private function createHouse(int $id, string $name, int $price, int $capacity, int $distance, string $amenities, bool $available): House
    {
        $house = new House($name, $price, $capacity, $distance, $amenities, $available);
        
        $reflection = new ReflectionClass($house);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($house, $id);
        
        return $house;
    }

    private function createBooking(int $id, User $user, House $house, string $comment, string $status): Booking
    {
        $booking = new Booking($user, $house, $comment, $status);
        
        $reflection = new ReflectionClass($booking);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($booking, $id);
        
        return $booking;
    }

    public function testCreateBookingSuccess(): void
    {
        // Arrange
        $comment = 'Test booking comment';
        
        $this->bookingRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Booking::class));

        // Act
        $booking = $this->bookingService->createBooking(
            $this->user, 
            $this->availableHouse, 
            $comment
        );

        // Assert
        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals($this->user, $booking->getGuest());
        $this->assertEquals($this->availableHouse, $booking->getHouse());
        $this->assertEquals($comment, $booking->getComment());
        $this->assertEquals('pending', $booking->getStatus());
    }

    public function testCreateBookingWithUnavailableHouse(): void
    {
        // Arrange
        $comment = 'Test booking comment';

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('House is not available');

        // Act
        $this->bookingService->createBooking(
            $this->user, 
            $this->unavailableHouse, 
            $comment
        );
    }

    public function testConfirmBookingSuccess(): void
    {
        // Arrange
        $bookingId = 1;
        $booking = $this->createBooking($bookingId, $this->user, $this->availableHouse, 'Test', 'pending');
        
        $this->bookingRepository
            ->method('findById')
            ->with($bookingId)
            ->willReturn($booking);

        $this->bookingRepository
            ->expects($this->once())
            ->method('save')
            ->with($booking);

        // Act
        $this->bookingService->confirmBooking($bookingId);

        // Assert
        $this->assertEquals('confirmed', $booking->getStatus());
    }

    public function testConfirmBookingNotFound(): void
    {
        // Arrange
        $bookingId = 999;
        
        $this->bookingRepository
            ->method('findById')
            ->with($bookingId)
            ->willReturn(null);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Booking not found');

        // Act
        $this->bookingService->confirmBooking($bookingId);
    }

    public function testCancelBookingSuccess(): void
    {
        // Arrange
        $bookingId = 1;
        $booking = $this->createBooking($bookingId, $this->user, $this->availableHouse, 'Test', 'pending');
        
        $this->bookingRepository
            ->method('findById')
            ->with($bookingId)
            ->willReturn($booking);

        $this->bookingRepository
            ->expects($this->once())
            ->method('save')
            ->with($booking);

        // Act
        $this->bookingService->cancelBooking($bookingId, $this->user);

        // Assert
        $this->assertEquals('cancelled', $booking->getStatus());
    }

    public function testCancelBookingNotFound(): void
    {
        // Arrange
        $bookingId = 999;
        
        $this->bookingRepository
            ->method('findById')
            ->with($bookingId)
            ->willReturn(null);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Booking not found');

        // Act
        $this->bookingService->cancelBooking($bookingId, $this->user);
    }

    public function testCancelBookingWrongUser(): void
    {
        // Arrange
        $bookingId = 1;
        $booking = $this->createBooking($bookingId, $this->user, $this->availableHouse, 'Test', 'pending');
        
        $this->bookingRepository
            ->method('findById')
            ->with($bookingId)
            ->willReturn($booking);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only cancel your own bookings');

        // Act
        $this->bookingService->cancelBooking($bookingId, $this->otherUser);
    }

    public function testCancelBookingInvalidStatus(): void
    {
        // Arrange
        $bookingId = 1;
        $booking = $this->createBooking($bookingId, $this->user, $this->availableHouse, 'Test', 'cancelled');
        
        $this->bookingRepository
            ->method('findById')
            ->with($bookingId)
            ->willReturn($booking);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This booking cannot be cancelled');

        // Act
        $this->bookingService->cancelBooking($bookingId, $this->user);
    }

    public function testGetUserBookings(): void
    {
        // Arrange
        $expectedBookings = [
            $this->createBooking(1, $this->user, $this->availableHouse, 'Booking 1', 'pending'),
            $this->createBooking(2, $this->user, $this->availableHouse, 'Booking 2', 'confirmed')
        ];
        
        $this->bookingRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($this->user)
            ->willReturn($expectedBookings);

        // Act
        $result = $this->bookingService->getUserBookings($this->user);

        // Assert
        $this->assertSame($expectedBookings, $result);
    }

    public function testGetPendingBookings(): void
    {
        // Arrange
        $expectedBookings = [
            $this->createBooking(1, $this->user, $this->availableHouse, 'Pending 1', 'pending')
        ];
        
        $this->bookingRepository
            ->expects($this->once())
            ->method('findByPending')
            ->willReturn($expectedBookings);

        // Act
        $result = $this->bookingService->getPendingBookings();

        // Assert
        $this->assertSame($expectedBookings, $result);
    }

    public function testGetHouseBookings(): void
    {
        // Arrange
        $expectedBookings = [
            $this->createBooking(1, $this->user, $this->availableHouse, 'House Booking', 'pending')
        ];
        
        $this->bookingRepository
            ->expects($this->once())
            ->method('findByHouse')
            ->with($this->availableHouse)
            ->willReturn($expectedBookings);

        // Act
        $result = $this->bookingService->getHouseBookings($this->availableHouse);

        // Assert
        $this->assertSame($expectedBookings, $result);
    }
}