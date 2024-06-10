<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\MicroPost;

class MicroPostVoter extends Voter
{

    /**
     * @param Security $security
     */
    public function __construct(
        private Security $security
    ) {}

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [MicroPost::EDIT, MicroPost::VIEW])
            && $subject instanceof MicroPost;
    }

    /**
     * @param string $attribute
     * @param MicroPost $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        $isAuthenticated = $user instanceof UserInterface;

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        switch ($attribute) {
            case MicroPost::EDIT:
                return $isAuthenticated && ($subject->getAuthor()->getId() === $user->getId() || $this->security->isGranted('ROLE_EDITOR'));
            case MicroPost::VIEW:
                return true;
        }

        return false;
    }
}
