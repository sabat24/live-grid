<?php

namespace App\Component\User\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

interface UserInterface extends PasswordAuthenticatedUserInterface
{
    public function getId(): ?int;

    public function getEmail(): ?string;

    public function setEmail(string $email): self;

    /**
     * @return list<string>
     */
    public function getRoles(): array;

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self;

    public function setPassword(string $password): self;

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
