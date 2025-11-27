<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\SecurityController;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/login',
            controller: SecurityController::class . '::login',
            description: 'Authenticate user with phone and password'
        ),
        new Post(
            uriTemplate: '/logout',
            controller: SecurityController::class . '::logout',
            description: 'Logout currently authenticated user'
        ),
    ]
)]
class Auth
{
}
