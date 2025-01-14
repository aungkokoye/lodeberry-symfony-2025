<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\VerifiedEmailException;
use App\Form\Registration\RegistrationFormType;
use App\Form\Registration\ResendEmailFormType;
use App\Form\Registration\ResetPasswordFormType;
use App\Security\EmailVerifier;
use App\Service\MailerManager;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

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
                'Please verify email!',
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

    #[Route('/verify-email/{hash}', name: 'app_verify_email', requirements: ['hash' => '.+'])]
    public function verifyUserEmail(string $hash): Response
    {
        // validate email confirmation link, sets User::isVerified=true and persists
        try {
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

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/resend-verify-email', name: 'app_resend_verify_email')]
    public function resendVerifyEmail(
        Request $request,
        UserManager $userManager,
        MailerManager $mailerManager
    ): Response {
        $form = $this->createForm(ResendEmailFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userManager->getUserByEmail($email);

            if ($user instanceof User) {

                    if ($user->isVerified()) {
                        $this->addFlash(
                            'resend-verify-email-success',
                            'User email have been verified!'
                        );
                    } else {
                        $userManager->createHash($user);

                        $mailerManager->sendUserVerifiedEmail(
                            $user,
                            'Please verify email!',
                            $this->renderView('registration/confirmation_email.html.twig', ['user' => $user])
                        );

                        $this->addFlash(
                            'register-success',
                            'Successfully Resend User Verified Email!' .
                            ' Please verify your email to activate your account and log in.'
                        );
                        return $this->redirectToRoute('app_login');
                    }

            } else {
                $this->addFlash(
                    'resend-verify-email-error',
                    "The email address {$email} does not belong to any existing user."
                );
                return $this->redirectToRoute('app_resend_verify_email');
            }


        }

        return $this->render('registration/resend_verify_email.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/reset-password-email', name: 'app_reset_password_email')]
    public function resetPasswordEmail(
        Request $request,
        UserManager $userManager,
        MailerManager $mailerManager
    ): Response
    {
        $form = $this->createForm(ResendEmailFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userManager->getUserByEmail($email);

            if ($user instanceof User) {
                $userManager->createHash($user);

                $mailerManager->sendUserVerifiedEmail(
                    $user,
                    'Please reset password!',
                    $this->renderView('registration/reset_password_confirmation_email.html.twig', ['user' => $user])
                );
            } else {
                $this->addFlash(
                    'reset-password-email-error',
                    "The email address {$email} does not belong to any existing user."
                );
            }

            return $this->redirectToRoute('app_reset_password_email');
        }

        return $this->render('registration/reset_password_email.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reset-password/{hash}', name: 'app_reset_password', requirements: ['hash' => '.+'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        string $hash
    ): Response
    {
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['hash' => $hash]);

        if (!$user instanceof User || in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->addFlash(
                'register-error',
                "User password reset email link fail!"
            );
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pass = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $pass));
            $user->setHashExpired(new \DateTime());
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'register-success',
                "Password is rested successfully!"
            );

            return $this->redirectToRoute('app_login');
        }


        return $this->render('registration/reset_password_email.html.twig', [
            'form' => $form,
        ]);
    }
}
