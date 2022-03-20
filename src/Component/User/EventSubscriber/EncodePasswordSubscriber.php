<?php

namespace App\Component\User\EventSubscriber;

use App\Component\User\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class EncodePasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    /**
     * @return  array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'app.user.pre_create' => ['encodePlainPassword', 0],
            'app.user.pre_update' => ['encodePlainPassword', 0],
        ];
    }

    public function encodePlainPassword(GenericEvent $event): void
    {

        $subject = $event->getSubject();

        if (!$subject instanceof UserInterface) {
            return;
        }

        if ($subject->getPlainPassword() === null) {
            return;
        }

        $subject->setPassword($this->userPasswordHasher->hashPassword($subject, $subject->getPlainPassword()));
    }
}
