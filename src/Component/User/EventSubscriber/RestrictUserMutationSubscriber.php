<?php

declare(strict_types=1);

namespace App\Component\User\EventSubscriber;

use App\Component\User\Security\UserManageVoter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class RestrictUserMutationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'app.user.pre_create' => ['denyUnlessGranted', 0],
            'app.user.pre_update' => ['denyUnlessGranted', 0],
            'app.user.pre_delete' => ['denyUnlessGranted', 0],
        ];
    }

    public function denyUnlessGranted(): void
    {
        if (!$this->authorizationChecker->isGranted(UserManageVoter::MANAGE)) {
            throw new AccessDeniedHttpException('User management is read-only.');
        }
    }
}
