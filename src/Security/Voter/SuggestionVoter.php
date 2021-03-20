<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Suggestion;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SuggestionVoter extends BaseVoter
{
    public const EDIT = 'editSuggestion';
    public const CREATE = 'createSuggestion';
    public const DELETE = 'deleteSuggestion';
    public const VIEW_LIST = 'viewSuggestionList';
    public const VIEW_ONE = 'viewOneSuggestion';

    private const SUPPORTED_ATTRIBUTES = [
        self::EDIT,
        self::CREATE,
        self::DELETE,
        self::VIEW_LIST,
        self::VIEW_ONE,
    ];

    /**
     * @param object $subject
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, self::SUPPORTED_ATTRIBUTES)
            && ($subject instanceof Suggestion or $subject == null);
    }

    /**
     * @param Suggestion|null $suggestion
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function voteOnAttribute(string $attribute, $suggestion, TokenInterface $token): bool
    {
        $this->user = $token->getUser() instanceof UserInterface
            ? $token->getUser()
            : null;

        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        switch ($attribute) {
            case self::EDIT:
            case self::DELETE:
                return $this->isLoggedIn() && $suggestion->getUser()->getId() === $this->user->getId();
            case self::VIEW_LIST:
            case self::CREATE:
            case self::VIEW_ONE:
                return $this->isLoggedIn();
        }

        return false;
    }
}
