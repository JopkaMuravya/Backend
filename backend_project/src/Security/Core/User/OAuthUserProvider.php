<?php

declare(strict_types=1);

namespace App\Security\Core\User;

use App\Entity\User;
use App\Services\OAuthUserManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthUserProvider implements OAuthAwareUserProviderInterface
{
    public function __construct(
        private OAuthUserManager $userManager
    ) {
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        return $this->userManager->findOrCreateUserFromYandex($response);
    }

    public function connect(UserInterface $user, UserResponseInterface $response): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Ожидается экземпляр %s, получен %s', User::class, get_class($user))
            );
        }

        $this->userManager->findOrCreateUserFromYandex($response);
    }
}