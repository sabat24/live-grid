<?php

namespace App\Component\User\Entity;

interface UserInterface
{
    public function getId(): ?int;

    public function getEmail(): ?string;

    public function setEmail(string $email): User;

    public function getRoles(): array;

    public function setRoles(array $roles): User;

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string;

    public function setPassword(string $password): User;

    public function getUsername(): ?string;

    public function setUsername(?string $username): void;

    public function getPlainPassword(): ?string;

    public function setPlainPassword(?string $plainPassword): void;

    public function getLastLogin(): ?\DateTimeInterface;

    public function setLastLogin(?\DateTimeInterface $lastLogin): void;

    public function isVerified(): bool;

    public function getVerifiedAt(): ?\DateTimeInterface;

    public function setVerifiedAt(?\DateTimeInterface $verifiedAt): void;
}
