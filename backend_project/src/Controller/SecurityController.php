<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/api')]
class SecurityController extends AbstractController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            return $this->json([
                'message' => 'Already logged in',
                'user' => [
                    'id' => $user->getId(),
                    'phone' => $user->getPhone(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                ],
            ]);
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastPhone = $authenticationUtils->getLastUsername();

        $responseData = [
            'error' => $error ? $error->getMessage() : 'Authentication failed',
            'message' => 'Invalid phone or password',
        ];

        if ($lastPhone) {
            $responseData['last_phone'] = $lastPhone;
        }

        return $this->json($responseData, Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return $this->json(['message' => 'Logged out successfully']);
    }
}
