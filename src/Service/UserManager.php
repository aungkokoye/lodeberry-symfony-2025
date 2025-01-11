<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    )
    {

    }

    public function createHash(User $user) : void
    {
        $currentDateTime = new \DateTime();
        $currentTimeString = $currentDateTime->format('d-m-Y H:i:s');

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user, // Pass the User entity or class implementing PasswordAuthenticatedUserInterface
            $user->getEmail() . $currentTimeString
        );

        $dateTime = new \DateTime();
        $dateTime->modify('+1 hour');

        $user->setHashExpired($dateTime)
             ->setHash($hashedPassword);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
    }
}