<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user command',
)]
class CreateUserCommand extends Command
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password',  InputOption::VALUE_REQUIRED, 'User password');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $user1 = new User();
        $user1->setEmail($email);
        $user1->setPassword($this->userPasswordHasher->hashPassword($user1, $password));

        $this->entityManager->persist($user1);
        $this->entityManager->flush();

        $io->success(sprintf('User %s account was created.',$email));

        return Command::SUCCESS;
    }
}
