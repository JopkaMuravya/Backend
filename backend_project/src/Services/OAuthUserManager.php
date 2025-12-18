<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OAuthUserManager
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function findOrCreateUserFromYandex(UserResponseInterface $response): User
    {
        $yandexId = $response->getUsername();
        
        $user = $this->userRepository->findOneBy(['yandexId' => $yandexId]);
        
        if ($user) {
            $this->updateUserFromYandex($user, $response);
            return $user;
        }
        
        $data = $response->getData();
        $phone = $this->extractPhoneFromYandexData($data);
        
        if ($phone) {
            $user = $this->userRepository->findOneBy(['phone' => $phone]);
            
            if ($user) {
                $user->setYandexId($yandexId);
                $this->updateUserFromYandex($user, $response);
                $this->entityManager->flush();
                return $user;
            }
        }
        
        $email = $response->getEmail();
        if ($email) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            
            if ($user) {
                $user->setYandexId($yandexId);
                $this->updateUserFromYandex($user, $response);
                $this->entityManager->flush();
                return $user;
            }
        }
        
        $user = $this->createUserFromYandex($response);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    private function extractPhoneFromYandexData(array $data): ?string
    {
        error_log('Yandex OAuth data: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
        
        if (isset($data['default_phone']['number'])) {
            $phone = $data['default_phone']['number'];
            $normalized = preg_replace('/\D/', '', $phone);
            return $normalized ?: $phone;
        }
        
        return null;
    }

    private function createUserFromYandex(UserResponseInterface $response): User
    {
        $yandexId = $response->getUsername();
        $email = $response->getEmail();
        
        $data = $response->getData();
        $phone = $this->extractPhoneFromYandexData($data);
        
        if (!$phone) {
            throw new \RuntimeException('Яндекс не предоставил номер телефона');
        }
        
        $realName = $response->getRealName() ?? '';
        $firstName = '';
        $lastName = '';
        
        if ($realName) {
            $nameParts = explode(' ', $realName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
        }
        
        if (empty($firstName)) {
            $firstName = 'Пользователь';
            $lastName = 'Яндекса';
        }
        
        return User::createFromYandex($yandexId, $phone, $email, $firstName, $lastName);
    }

    private function updateUserFromYandex(User $user, UserResponseInterface $response): void
    {
        $email = $response->getEmail();
        $realName = $response->getRealName() ?? '';
        
        $data = $response->getData();
        $phone = $this->extractPhoneFromYandexData($data);
        if ($phone && $user->getPhone() !== $phone) {
            $existingUser = $this->userRepository->findOneBy(['phone' => $phone]);
            if (!$existingUser || $existingUser->getId() === $user->getId()) {
                $user->setPhone($phone);
            }
        }
        
        if ($email && $user->getEmail() !== $email) {
            $user->setEmail($email);
        }
        
        if ($realName) {
            $nameParts = explode(' ', $realName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            if ($firstName && $user->getFirstName() !== $firstName) {
                $user->setFirstName($firstName);
            }
            
            if ($lastName && $user->getLastName() !== $lastName) {
                $user->setLastName($lastName);
            }
        }
        
        $this->entityManager->flush();
    }
}