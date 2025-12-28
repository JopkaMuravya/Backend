<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
            throw new HttpException(400, 'Нужны номер телефона и ID домика');
        }

        $booking = Booking::createNew(
            (int)$data['house_id'],
            $data['guest_name'] ?? '',
            $data['guest_phone'],
            $data['comment'] ?? ''
        );

        $result = $this->bookingRepository->save($booking);

        if (!$result) {
            throw new HttpException(500, 'Ошибка при создании заявки');
        }

        return $this->json([
            'success' => true,
            'message' => 'Заявка создана!',
            'booking_id' => $booking->getId()
        ], 201);
    }

    #[Route('/bookings/{id}', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (empty($data['comment'])) {
            throw new HttpException(400, 'Комментарий не может быть пустым');
        }

        $booking = $this->bookingRepository->findById($id);
        if (!$booking) {
            throw new HttpException(404, 'Заявка не найдена');
        }

        $booking->setComment($data['comment']);
        
        $result = $this->bookingRepository->save($booking);

        if (!$result) {
            throw new HttpException(500, 'Ошибка при обновлении');
        }

        return $this->json([
            'success' => true,
            'message' => 'Комментарий обновлен!'
        ]);
    }

    #[Route('/bookings/{id}', methods: ['DELETE'])]
    public function deleteBooking(int $id): JsonResponse
    {
        $result = $this->bookingRepository->delete($id);

        if (!$result) {
            throw new HttpException(404, 'Заявка не найдена');
        }

        return $this->json([
            'success' => true,
            'message' => 'Заявка удалена!'
        ]);
    }
}