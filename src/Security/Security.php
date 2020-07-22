<?php

/*
 * This file is part of Symfony Boilerplate.
 *
 * (c) Saif Eddin Gmati
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security as SymfonySecurity;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

final class Security implements AuthorizationCheckerInterface
{
    public const FIREWALL = 'main';

    public const ACCESS_DENIED_ERROR = SymfonySecurity::ACCESS_DENIED_ERROR;

    public const AUTHENTICATION_ERROR = SymfonySecurity::AUTHENTICATION_ERROR;

    public const LAST_USERNAME = SymfonySecurity::LAST_USERNAME;

    public const MAX_USERNAME_LENGTH = SymfonySecurity::MAX_USERNAME_LENGTH;

    private AuthorizationCheckerInterface $authorizationChecker;

    private TokenStorageInterface $tokenStorage;

    private GuardAuthenticatorHandler $guardAuthenticatorHandler;

    private UrlGeneratorInterface $urlGenerator;

    private Authenticator $authenticator;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        GuardAuthenticatorHandler $guardAuthenticatorHandler,
        UrlGeneratorInterface $urlGenerator,
        Authenticator $authenticator
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->guardAuthenticatorHandler = $guardAuthenticatorHandler;
        $this->urlGenerator = $urlGenerator;
        $this->authenticator = $authenticator;
    }

    /**
     * Returns whether there's an authenticated user.
     */
    public function isAuthenticated(): bool
    {
        return null !== $this->getUser();
    }

    /**
     * Returns the current security token.
     */
    public function getToken(): ?TokenInterface
    {
        return $this->tokenStorage->getToken();
    }

    /**
     * Returns the current authenticated user.
     */
    public function getUser(): ?User
    {
        $token = $this->getToken();

        if (null === $token) {
            return null;
        }

        $user = $token->getUser();

        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     *
     * @param mixed $attribute A single attribute to vote on (can be of any type, string and instance of Expression are
     *                         supported by the core)
     * @param mixed $subject
     */
    public function isGranted($attribute, $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $subject);
    }

    public function invalidate(SessionInterface $session): void
    {
        if (!$this->isAuthenticated()) {
            return;
        }

        $this->tokenStorage->setToken(null);
        $session->invalidate();
    }

    /**
     * Convenience method for authenticating the user and returning the
     * Response *if any* for success.
     */
    public function authenticate(User $user, Request $request, string $firewall = self::FIREWALL): Response
    {
        $response = $this->guardAuthenticatorHandler->authenticateUserAndHandleSuccess($user, $request, $this->authenticator, $firewall);
        $response ??= new RedirectResponse($this->urlGenerator->generate('index'));

        return $response;
    }
}
