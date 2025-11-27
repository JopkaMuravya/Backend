<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use App\Entity\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByPhone(string $phone): ?User;
    public function findAll(): array;
    public function save(User $user): void;
    public function remove(User $user): void;
}
