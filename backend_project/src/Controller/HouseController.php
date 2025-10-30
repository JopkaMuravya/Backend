<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HouseRepository;
use App\Repository\BookingRepository;

#[Route('/api')]
class HouseController extends AbstractController
{
    public function __construct(
        private HouseRepository $houseRepository,
        private BookingRepository $bookingRepository
    ) {}

    #[Route('/available-houses', methods: ['GET'])]
    public function getAvailableHouses(): JsonResponse
    {
        $houses = $this->houseRepository->findAll();
        
        $freeHouses = [];
        foreach ($houses as $house) {
            $bookings = $this->bookingRepository->findByHouseId($house->getId());
            
            $hasActiveBooking = false;
            foreach ($bookings as $booking) {
                if ($booking->isActive()) {
                    $hasActiveBooking = true;
                    break;
                }
            }
            
            if (!$hasActiveBooking) {
                $freeHouses[] = $house->toArray();
            }
        }

        return $this->json([
            'success' => true,
            'data' => $freeHouses,
            'count' => count($freeHouses)
        ]);
    }
}