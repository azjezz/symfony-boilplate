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

use App\Entity\User;
use App\Security\Security;
use App\Service\Registration;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * @Route("/user")
 */
final class RegisterController
{
    private Registration $registration;

    private Environment $twig;

    private UrlGeneratorInterface $urlGenerator;

    private Security $security;

    public function __construct(Environment $twig, Registration $registration, Security $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->registration = $registration;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
    }

    /**
     * @Route("/register", methods={"GET", "POST"}, name="user_register")
     */
    public function register(Request $request): Response
    {
        if ($this->security->isAuthenticated()) {
            $url = $this->urlGenerator->generate('index');

            return new RedirectResponse($url);
        }

        $user = new User();
        $form = $this->registration->createForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->registration->register($user);

            return $this->security->authenticate($user, $request);
        }

        $content = $this->twig->render('user/register.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true),
        ]);

        return new Response($content);
    }
}
