<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\User;
use App\Entity\House;
use App\Repository\Interfaces\BookingRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookingRepository extends ServiceEntityRepository implements BookingRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findById(int $id): ?Booking
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['guest' => $user], ['createdAt' => 'DESC']);
    }

    public function findByPending(): array
    {
        return $this->findBy(['status' => 'pending'], ['createdAt' => 'ASC']);
    }

    public function findByHouse(House $house): array
    {
        return $this->findBy(['house' => $house], ['createdAt' => 'DESC']);
    }

    public function save(Booking $booking): void
    {
        $this->getEntityManager()->persist($booking);
        $this->getEntityManager()->flush();
    }

    public function remove(Booking $booking): void
    {
        $this->getEntityManager()->remove($booking);
        $this->getEntityManager()->flush();
    }
}