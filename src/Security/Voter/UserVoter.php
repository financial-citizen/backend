<?php declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Users;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends BaseVoter
{
    public const EDIT_PASSWORD = 'editPassword';
    public const EDIT_PROFILE = 'editProfile';
    public const EDIT_PROFILE_PICTURE = 'editProfilePicture';
    public const EDIT_PROFILE_SETTINGS = 'editProfileSettings';
    public const DELETE_USER = 'deleteUser';
    public const VIEW_ME = 'viewMe';
    public const VIEW_LIST = 'viewList';
    public const VIEW_PROFILE = 'viewProfile';

    private const SUPPORTED_ATTRIBUTES = [
        self::EDIT_PASSWORD,
        self::EDIT_PROFILE,
        self::EDIT_PROFILE_PICTURE,
        self::EDIT_PROFILE_SETTINGS,
        self::DELETE_USER,
        self::VIEW_ME,
        self::VIEW_LIST,
        self::VIEW_PROFILE,
    ];

    /**
     * @param object $subject
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, self::SUPPORTED_ATTRIBUTES)
            && ($subject instanceof Users || $subject == null);
    }

    /**
     * @param Users|null $user
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function voteOnAttribute(string $attribute, $user, TokenInterface $token): bool
    {
        $this->user = $token->getUser() instanceof UserInterface
            ? $token->getUser()
            : null;

        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW_LIST:
                return $this->isLoggedIn();
            case self::EDIT_PASSWORD:
            case self::EDIT_PROFILE:
            case self::EDIT_PROFILE_PICTURE:
            case self::EDIT_PROFILE_SETTINGS:
            case self::DELETE_USER:
                return $this->canEdit($user);
            case self::VIEW_ME:
                return $this->canViewMe($user);
            case self::VIEW_PROFILE:
                return true;
        }

        return false;
    }

    private function canEdit(Users $user): bool
    {
        return $this->canViewMe($user);
    }

    private function canViewMe(Users $user): bool
    {
        return $user instanceof UserInterface
            && $this->isLoggedIn()
            && $this->user->getId() === $user->getId();
    }
}
