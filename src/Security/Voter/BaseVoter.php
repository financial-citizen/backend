<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Users;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseVoter extends Voter
{
    protected AccessDecisionManagerInterface $decisionManager;

    /** @var Users|UserInterface|null */
    protected ?UserInterface $user;

    protected TokenInterface $token;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function isLoggedIn(): bool
    {
        return $this->user instanceof UserInterface;
    }

    protected function isAdmin(): bool
    {
        return $this->decisionManager->decide($this->token, array('ROLE_ADMIN'));
    }
}
