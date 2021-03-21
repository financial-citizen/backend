<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Suggestion;
use App\Entity\Vote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class VoteVoter extends BaseVoter
{
    public const VOTE = 'vote';

    private const SUPPORTED_ATTRIBUTES = [
        self::VOTE,
    ];

    /**
     * @param object $subject
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, self::SUPPORTED_ATTRIBUTES)
            && ($subject instanceof Vote or $subject == null);
    }

    /**
     * @param Vote|null $vote
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function voteOnAttribute(string $attribute, $vote, TokenInterface $token): bool
    {
        $this->user = $token->getUser() instanceof UserInterface
            ? $token->getUser()
            : null;

        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        switch ($attribute) {
            case self::VOTE:
                return $this->isLoggedIn() && $vote->getUser()->getId() === $this->user->getId();
        }

        return false;
    }
}
