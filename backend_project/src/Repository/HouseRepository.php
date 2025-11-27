<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\House;
use App\Repository\Interfaces\HouseRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HouseRepository extends ServiceEntityRepository implements HouseRepositoryInterface
{
    /**
     * @psalm-suppress PossiblyUnusedParam
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, House::class);
    }

    public function findById(int $id): ?House
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function findAvailableHouses(): array
    {
        return $this->findBy(['isAvailable' => true]);
    }

    public function save(House $house): void
    {
        $this->getEntityManager()->persist($house);
        $this->getEntityManager()->flush();
    }

    public function remove(House $house): void
    {
        $this->getEntityManager()->remove($house);
        $this->getEntityManager()->flush();
    }
}
