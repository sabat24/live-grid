<?php

declare(strict_types=1);

namespace App\Component\User\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, mixed>
 */
final class UserManageVoter extends Voter
{
    public const string MANAGE = 'app.user.manage';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::MANAGE;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return \in_array('ROLE_USER_MANAGER', $token->getRoleNames(), true);
    }
}
