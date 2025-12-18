<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\UserController;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\Expr\Func;
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

    #[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
    private ?string $yandexId = null;

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

    public static function createFromYandex(
        string $yandexId,
        string $phone,
        string $email,
        string $firstName,
        string $lastName
    ): self {
        $tempPassword = bin2hex(random_bytes(16));
        
        $user = new self(
            phone: $phone,
            password: $tempPassword,
            firstName: $firstName,
            lastName: $lastName,
            email: $email
        );
        
        $user->setYandexId($yandexId);
        
        return $user;
    }

    public function __toString(): string
    {
        return (string) $this->id;
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

    public function getYandexId(): ?string
    {
        return $this->yandexId;
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
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
    public function setYandexId(?string $yandexId): void
    {
        $this->yandexId = $yandexId;
    }
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    //Бизнес-логика
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function updateFromYandex(string $phone, string $email, string $firstName, string $lastName): void
    {
        $this->phone = $phone;
        if ($email) {
            $this->email = $email;
        }
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function isYandexUser(): bool
    {
        return $this->yandexId !== null;
    }
}
