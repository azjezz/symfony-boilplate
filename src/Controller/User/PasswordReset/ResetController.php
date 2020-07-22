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

use App\Form\User\PasswordReset\PasswordResetType;
use App\Security\Security;
use App\Service\PasswordReset;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Twig\Environment;

/**
 * @Route("/user/password-reset")
 */
final class ResetController
{
    private PasswordReset $passwordReset;

    private FormFactoryInterface $factory;

    private Environment $twig;

    private Security $security;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(Environment $twig, FormFactoryInterface $factory, PasswordReset $passwordReset, Security $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->passwordReset = $passwordReset;
        $this->factory = $factory;
        $this->twig = $twig;
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/reset/{token}", methods={"POST", "GET"}, name="user_password_reset")
     */
    public function reset(Request $request, ?string $token = null): Response
    {
        if ($token) {
            return $this->passwordReset->storeTokenInSession($request->getSession(), $token);
        }

        $token = $this->passwordReset->getTokenFromSession($request->getSession());

        try {
            $user = $this->passwordReset->retrieveUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            /** @var FlashBagInterface $flashes */
            $flashes = $request->getSession()->getBag('flashes');
            $flashes->add(PasswordReset::RESET_PASSWORD_ERROR, $e->getReason());

            $url = $this->urlGenerator->generate('user_password_reset_request');

            return new RedirectResponse($url);
        }

        // The token is valid; allow the user to change their password.
        $form = $this->factory->create(PasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->passwordReset->resetPassword($request, $form, $user, $token);

            return $this->security->authenticate($user, $request);
        }

        $content = $this->twig->render('user/reset_password/reset.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true),
        ]);

        return new Response($content);
    }
}
