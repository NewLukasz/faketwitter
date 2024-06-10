<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MicroPost;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {}

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $user1 = new User();
        $user1->setEmail('test1@test.com');
        $user1->setPassword($this->userPasswordHasher->hashPassword($user1,'1234'));

        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('test2@test.com');
        $user2->setPassword($this->userPasswordHasher->hashPassword($user2,'1234'));

        $manager->persist($user2);


        $microPost1 = new MicroPost();
        $microPost1->setTitle('Welcome to Poland');
        $microPost1->setText('Welcome to Poland');
        $microPost1->setCreated(new DateTime());
        $microPost1->setAuthor($user1);
        $manager->persist($microPost1);

        $microPost2 = new MicroPost();
        $microPost2->setTitle('Welcome to US');
        $microPost2->setText('Welcome to US');
        $microPost2->setCreated(new DateTime());
        $microPost2->setAuthor($user2);
        $manager->persist($microPost2);

        $microPost3 = new MicroPost();
        $microPost3->setTitle('Welcome to Germany');
        $microPost3->setText('Welcome to Germany');
        $microPost3->setCreated(new DateTime());
        $microPost3->setAuthor($user1);
        $manager->persist($microPost3);

        $manager->flush();
    }
}
