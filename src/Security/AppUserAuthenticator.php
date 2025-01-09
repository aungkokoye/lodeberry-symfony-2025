<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppUserAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $this->getEmail($request);

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Fetch user (assumes UserProvider is correctly configured)
        $user = $this->getUser($email);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('User not found.');
        }

        if ($this->reachMaxLoginAttempt($user) ) {
            throw new CustomUserMessageAuthenticationException('Reach maximum login attempt. try again after 1 hour.');
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException('Your account email has not verified yet.');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $email = $this->getEmail($request);
        $user = $this->getUser($email);
        $this->updateLoginAttemptData($user, 0);

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $email = $this->getEmail($request);
        $user = $this->getUser($email);
        $this->updateLoginAttemptData($user, $user->getLoginAttempts() + 1);

        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    protected function reachMaxLoginAttempt(User $user): bool
    {
        $currentTime = new \DateTime();
        $timePlusOneHour = clone $user->getLastLoginAt();
        $timePlusOneHour->modify('+1 hour');
        if ($currentTime > $timePlusOneHour) {
            return false;
        }

        if ($user->getLoginAttempts() < User::MAX_LOGIN_ATTEMPT) {
            return false;
        }

        return true;
    }

    protected function getEmail(Request $request): ?string
    {
        return $request->getPayload()->getString('email');
    }

    protected function getUser(string $email): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    protected function updateLoginAttemptData(User $user, int $loginAttempts): void
    {
        $user->setLoginAttempts($loginAttempts);
        $user->setLastLoginAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
    }
}
