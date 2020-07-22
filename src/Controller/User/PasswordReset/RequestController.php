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

use App\Form\User\PasswordReset\PasswordResetRequestType;
use App\Service\PasswordReset;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\Error as TwigError;

/**
 * @Route("/user/password-reset")
 */
final class RequestController
{
    private FormFactoryInterface $form;

    private PasswordReset $passwordReset;

    private Environment $twig;

    public function __construct(Environment $twig, FormFactoryInterface $form, PasswordReset $passwordReset)
    {
        $this->form = $form;
        $this->passwordReset = $passwordReset;
        $this->twig = $twig;
    }

    /**
     * @Route("/", methods={"GET", "POST"}, name="user_password_reset_request")
     *
     * @throws TwigError
     */
    public function request(Request $request, Session $session): Response
    {
        $form = $this->form->create(PasswordResetRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $address */
            $address = $form->get(PasswordResetRequestType::EMAIL_FIELD)->getData();

            return $this->passwordReset->sendPasswordResetEmail($session, $address);
        }

        $content = $this->twig->render('user/reset_password/request.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true),
        ]);

        return new Response($content);
    }
}
