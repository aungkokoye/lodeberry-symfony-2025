<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:admin-user-create',
    description: 'Create admin user',
)]
class AdminUserCreateCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin user email')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin user password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $output->writeln([
            'Admin User Creator',
            '====================',
            '',
        ]);
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');

        if( strlen($plainPassword) < 6) {
            $io->error("Error: password should be at least 8 characters.");
            return Command::FAILURE;
        }

        $newAdminUser = new User();
        $hashPassword  = $this->passwordHasher->hashPassword(
            $newAdminUser,
            $plainPassword
        );
        try {
            $newAdminUser->setEmail($email)
                ->setRoles(["USER", "ADMIN"])
                ->setPassword($hashPassword)
                ->setIsVerified(1)
                ->setLoginAttempts(0)
            ;

            $violations = $this->validator->validate($newAdminUser);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $io->error("Error: {$violation->getMessage()} .");
                }
                return Command::FAILURE;
            }

            $this->em->persist($newAdminUser);
            $this->em->flush();
            $io->success("You have successfully create Admin User : ($email).");

            return Command::SUCCESS;
        } catch (\Exception $ex) {
            $io->error("Error: {$ex->getMessage()} .");
        }

        return Command::FAILURE;
    }
}
