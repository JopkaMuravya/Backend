<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BookingRepository;
use App\Entity\Booking;

#[Route('/api')]
class BookingController extends AbstractController
{
    public function __construct(
        private BookingRepository $bookingRepository
    ) {}

    #[Route('/bookings', methods: ['POST'])]
    public function createBooking(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (empty($data['guest_phone']) || empty($data['house_id'])) {
            return $this->json([
                'success' => false,
                'error' => 'Нужны номер телефона и ID домика'
            ], 400);
        }

        $booking = Booking::createNew(
            (int)$data['house_id'],
            $data['guest_name'] ?? '',
            $data['guest_phone'],
            $data['comment'] ?? ''
        );

        $result = $this->bookingRepository->save($booking);

        if ($result) {
            return $this->json([
                'success' => true,
                'message' => 'Заявка создана!',
                'booking_id' => $booking->getId()
            ], 201);
        } else {
            return $this->json([
                'success' => false,
                'error' => 'Ошибка при создании заявки'
            ], 500);
        }
    }

    #[Route('/bookings/{id}', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (empty($data['comment'])) {
            return $this->json([
                'success' => false,
                'error' => 'Комментарий не может быть пустым'
            ], 400);
        }

        $booking = $this->bookingRepository->findById($id);
        if (!$booking) {
            return $this->json([
                'success' => false,
                'error' => 'Заявка не найдена'
            ], 404);
        }

        $booking->setComment($data['comment']);
        
        $result = $this->bookingRepository->save($booking);

        if ($result) {
            return $this->json([
                'success' => true,
                'message' => 'Комментарий обновлен!'
            ]);
        } else {
            return $this->json([
                'success' => false,
                'error' => 'Ошибка при обновлении'
            ], 500);
        }
    }

    #[Route('/bookings/{id}', methods: ['DELETE'])]
    public function deleteBooking(int $id): JsonResponse
    {
        $result = $this->bookingRepository->delete($id);

        if ($result) {
            return $this->json([
                'success' => true,
                'message' => 'Заявка удалена!'
            ]);
        } else {
            return $this->json([
                'success' => false,
                'error' => 'Заявка не найдена'
            ], 404);
        }
    }
}