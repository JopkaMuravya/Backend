<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\UserController;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
#[ORM\Table(name: 'users')]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/users',
            controller: UserController::class . '::list',
            description: 'Get all users (admin only)'
        ),
        new Get(
            uriTemplate: '/users/{id}',
            controller: UserController::class . '::show',
            description: 'Get user by ID (admin only)'
        ),
        new Post(
            uriTemplate: '/register',
            controller: UserController::class . '::register',
            description: 'Register new user'
        ),
        new Get(
            uriTemplate: '/profile',
            controller: UserController::class . '::profile',
            description: 'Get current user profile'
        ),
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    private string $phone;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 100)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100)]
    private string $lastName;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function __construct(
        string $email,
        string $phone,
        string $password,
        string $firstName,
        string $lastName,
        array $roles = ['ROLE_USER'],
    ) {
        $this->email = $email;
        $this->phone = $phone;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->roles = $roles;
    }

    // Геттеры
    public function getId(): int
    {
        return $this->id;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getPhone(): string
    {
        return $this->phone;
    }
    #[Override]
    public function getPassword(): string
    {
        return $this->password;
    }
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    public function getLastName(): string
    {
        return $this->lastName;
    }
    #[Override]
    public function getRoles(): array
    {
        return $this->roles;
    }

    // Методы UserInterface
    #[Override]
    public function getUserIdentifier(): string
    {
        return $this->phone;
    }
    #[Override]
    public function eraseCredentials(): void
    {
    }
    public function getSalt(): ?string
    {
        return null;
    }

    // Сеттеры
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    //Бизнес-логика
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
