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

namespace App\Controller\User;

use App\Security\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

/**
 * @Route("/user")
 */
final class LoginController
{
    private AuthenticationUtils $authenticationUtils;

    private Environment $twig;

    private Security $security;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(Environment $twig, Security $security, AuthenticationUtils $authenticationUtils, UrlGeneratorInterface $urlGenerator)
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->twig = $twig;
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/login", methods={"POST", "GET"}, name="user_login")
     */
    public function login(): Response
    {
        if ($this->security->isAuthenticated()) {
            $url = $this->urlGenerator->generate('index');

            return new RedirectResponse($url);
        }

        $content = $this->twig->render('user/login.html.twig', [
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);

        return new Response($content);
    }
}
