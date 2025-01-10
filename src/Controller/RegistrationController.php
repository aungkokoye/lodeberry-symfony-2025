<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\VerifiedEmailException;
use App\Form\RegistrationFormType;
use App\Security\AppUserAuthenticator;
use App\Security\EmailVerifier;
use App\Service\MailerManager;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private EntityManagerInterface $em
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        UserManager $userManager,
        MailerManager $mailerManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $userManager->createHash($user);
            $mailerManager->sendUserVerifiedEmail(
                $user,
                $this->renderView('registration/confirmation_email.html.twig', ['user' => $user])
            );


            $this->addFlash(
                'register-success',
                'Registration successful! A confirmation email has been sent to your email address.' .
                ' Please verify your email to activate your account and log in.'
            );

            return $this->redirectToRoute('app_login');

            // auto authenticate after register
            //return $security->login($user,  "debug.App\Security\AppUserAuthenticator", 'main');
        }



        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $hash = $request->query->get('hash');
            /** @var User $user */
            $user =$this->em->getRepository(User::class)->findOneBy(['hash' => $hash]);
            $this->emailVerifier->handleEmailConfirmation($user);
        } catch (VerifiedEmailException $exception) {
            $this->addFlash('verify-email-error', $exception->getMessage());

            return $this->redirectToRoute('app_register');
        }

        $user->setVerified(1);
        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash(
            'register-success',
            'Email Verification successful! Please login.'
        );

        return $this->redirectToRoute('app_login');
    }

}
