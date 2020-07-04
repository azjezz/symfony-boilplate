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

namespace App\Controller\User\PasswordReset;

use App\Service\PasswordReset;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * @Route("/user/password-reset")
 */
final class ConfirmationController
{
    private PasswordReset $passwordReset;

    private Environment $twig;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(Environment $twig, PasswordReset $passwordReset, UrlGeneratorInterface $urlGenerator)
    {
        $this->passwordReset = $passwordReset;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Confirmation page after a user has requested a password reset.
     *
     * @Route("/confirm", methods={"GET", "POST"}, name="user_password_reset_confirm")
     */
    public function confirm(Request $request): Response
    {
        // We prevent users from directly accessing this page
        if (!$this->passwordReset->canCheckEmail($request->getSession())) {
            $url = $this->urlGenerator->generate('user_password_reset_request');

            return new RedirectResponse($url);
        }

        $content = $this->twig->render('user/reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->passwordReset->helper->getTokenLifetime(),
        ]);

        return new Response($content);
    }
}
