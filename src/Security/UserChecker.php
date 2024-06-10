<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Entity\User as AppUser;
use DateTime;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @param User $user
     * @return void
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if( null === $user->getBannedUntil()){
            return;
        }

        $now = new DateTime();
        if($now < $user->getBannedUntil()){
            throw new AccessDeniedHttpException('The user is banned');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
//        if (!$user instanceof AppUser) {
//            return;
//        }
//
//        // user account is expired, the user may be notified
//        if ($user->isExpired()) {
//            throw new AccountExpiredException('...');
//        }
    }
}