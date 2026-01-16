<?php
declare(strict_types=1);

final class User
{
    public function __construct(
        private int $id,
        private string $pseudo,
        private string $email,
        private ?string $avatar,
        private ?string $createdAt = null,
        private ?string $passwordHash = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            (string)$row['pseudo'],
            (string)($row['email'] ?? ''),
            $row['avatar'] ?? null,
            $row['created_at'] ?? null,
            $row['password'] ?? null
        );
    }

    public function id(): int { return $this->id; }
    public function pseudo(): string { return $this->pseudo; }
    public function email(): string { return $this->email; }
    public function avatar(): ?string { return $this->avatar; }
    public function createdAt(): ?string { return $this->createdAt; }
    public function passwordHash(): ?string { return $this->passwordHash; }

    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'pseudo' => $this->pseudo,
            'email' => $this->email,
        ];
    }
}
