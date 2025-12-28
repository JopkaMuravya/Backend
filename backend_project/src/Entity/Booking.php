<?php

namespace App\Entity;

class Booking
{
    public function __construct(
        private int $id,
        private int $houseId,
        private string $guestName,
        private string $guestPhone,
        private string $comment,
        private string $status,
        private string $createdAt,
        private string $updatedAt
    ) {}

    // Геттеры
    public function getId(): int { return $this->id; }
    public function getHouseId(): int { return $this->houseId; }
    public function getGuestName(): string { return $this->guestName; }
    public function getGuestPhone(): string { return $this->guestPhone; }
    public function getComment(): string { return $this->comment; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    // Сеттеры
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    // Бизнес-логика
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function isActive(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPhoneValid(): bool
    {
        return preg_match('/^\+7\d{10}$/', $this->guestPhone) === 1;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'house_id' => $this->houseId,
            'guest_name' => $this->guestName,
            'guest_phone' => $this->guestPhone,
            'comment' => $this->comment,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['house_id'],
            $data['guest_name'],
            $data['guest_phone'],
            $data['comment'],
            $data['status'],
            $data['created_at'],
            $data['updated_at']
        );
    }

    public static function createNew(int $houseId, string $guestName, string $guestPhone, string $comment): self
    {
        $now = date('Y-m-d H:i:s');
        return new self(0, $houseId, $guestName, $guestPhone, $comment, 'pending', $now, $now);
    }
}