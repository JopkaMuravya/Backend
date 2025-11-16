<?php

namespace App\Controller;

use App\Repository\Interfaces\HouseRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class HouseController extends AbstractController
{
    public function __construct(
        private HouseRepositoryInterface $houseRepository
    ) {
    }

    #[Route('/houses', name: 'api_house_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $houses = $this->houseRepository->findAll();

        $data = array_map(function ($house) {
            return [
                'id' => $house->getId(),
                'name' => $house->getName(),
                'pricePerNight' => $house->getPricePerNight(),
                'capacity' => $house->getCapacity(),
                'distanceToSea' => $house->getDistanceToSea(),
                'amenities' => $house->getAmenities(),
                'isAvailable' => $house->isAvailable()
            ];
        }, $houses);

        return $this->json($data);
    }

    #[Route('/houses/available', name: 'api_house_available', methods: ['GET'])]
    public function available(): JsonResponse
    {
        $houses = $this->houseRepository->findAvailableHouses();

        $data = array_map(function ($house) {
            return [
                'id' => $house->getId(),
                'name' => $house->getName(),
                'pricePerNight' => $house->getPricePerNight(),
                'capacity' => $house->getCapacity()
            ];
        }, $houses);

        return $this->json($data);
    }

    #[Route('/houses/{id}', name: 'api_house_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $house = $this->houseRepository->findById($id);

        if (!$house) {
            return $this->json(['error' => 'House not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $house->getId(),
            'name' => $house->getName(),
            'pricePerNight' => $house->getPricePerNight(),
            'capacity' => $house->getCapacity(),
            'distanceToSea' => $house->getDistanceToSea(),
            'amenities' => $house->getAmenities(),
            'isAvailable' => $house->isAvailable()
        ];

        return $this->json($data);
    }
}
