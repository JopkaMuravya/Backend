<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\BookingRepository")]
#[ORM\Table(name: "bookings")]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: "User")]
    #[ORM\JoinColumn(nullable: false)]
    private User $guest;

    #[ORM\ManyToOne(targetEntity: "House")]
    #[ORM\JoinColumn(nullable: false)]
    private House $house;

    #[ORM\Column(type: "text")]
    private string $comment;

    #[ORM\Column(type: "string", length: 20)]
    private string $status;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updatedAt;

    public function __construct(
        User $guest,
        House $house,
        string $comment,
        string $status = 'pending'
    ) {
        $this->guest = $guest;
        $this->house = $house;
        $this->comment = $comment;
        $this->status = $status;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Геттеры
    public function getId(): int
    {
        return $this->id;
    }
    public function getGuest(): User
    {
        return $this->guest;
    }
    public function getHouse(): House
    {
        return $this->house;
    }
    public function getComment(): string
    {
        return $this->comment;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    // Сеттеры
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
        $this->updatedAt = new \DateTime();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();
    }
}
