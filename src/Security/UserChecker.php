<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use DateTime;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @param UserInterface $user
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

    /**
     * @param UserInterface $user
     * @return void
     */
    public function checkPostAuth(UserInterface $user): void
    {}
}