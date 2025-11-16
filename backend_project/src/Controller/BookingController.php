<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Repository\Interfaces\BookingRepositoryInterface;
use App\Repository\Interfaces\HouseRepositoryInterface;
use App\Services\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class BookingController extends AbstractController
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private HouseRepositoryInterface $houseRepository,
        private BookingService $bookingService
    ) {
    }

    #[Route('/bookings', name: 'api_booking_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $bookings = $this->bookingRepository->findByUser($user);

        $data = array_map(function (Booking $booking) {
            return [
                'id' => $booking->getId(),
                'houseId' => $booking->getHouse()->getId(),
                'houseName' => $booking->getHouse()->getName(),
                'guestName' => $booking->getGuest()->getFullName(),
                'comment' => $booking->getComment(),
                'status' => $booking->getStatus(),
                'createdAt' => $booking->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $bookings);

        return $this->json($data);
    }

    #[Route('/bookings/pending', name: 'api_booking_pending', methods: ['GET'])]
    public function pending(): JsonResponse
    {
        $bookings = $this->bookingRepository->findByPending();

        $data = array_map(function (Booking $booking) {
            return [
                'id' => $booking->getId(),
                'houseId' => $booking->getHouse()->getId(),
                'houseName' => $booking->getHouse()->getName(),
                'guestName' => $booking->getGuest()->getFullName(),
                'comment' => $booking->getComment(),
                'createdAt' => $booking->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $bookings);

        return $this->json($data);
    }

    #[Route('/bookings', name: 'api_booking_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $houseId = $data['houseId'] ?? null;
        $comment = $data['comment'] ?? '';

        if (!$houseId) {
            return $this->json(['error' => 'houseId is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $house = $this->houseRepository->findById($houseId);
            if (!$house) {
                return $this->json(['error' => 'House not found'], Response::HTTP_NOT_FOUND);
            }

            $booking = $this->bookingService->createBooking($user, $house, $comment);

            return $this->json([
                'id' => $booking->getId(),
                'status' => $booking->getStatus(),
                'message' => 'Booking created successfully'
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/bookings/{id}/cancel', name: 'api_booking_cancel', methods: ['POST'])]
    public function cancel(int $id): JsonResponse
    {
        $user = $this->getUser();

        try {
            $this->bookingService->cancelBooking($id, $user);

            return $this->json(['message' => 'Booking cancelled successfully']);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/bookings/{id}', name: 'api_booking_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findById($id);

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $booking->getId(),
            'house' => [
                'id' => $booking->getHouse()->getId(),
                'name' => $booking->getHouse()->getName()
            ],
            'guest' => [
                'id' => $booking->getGuest()->getId(),
                'name' => $booking->getGuest()->getFullName()
            ],
            'comment' => $booking->getComment(),
            'status' => $booking->getStatus(),
            'createdAt' => $booking->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $booking->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return $this->json($data);
    }
}
