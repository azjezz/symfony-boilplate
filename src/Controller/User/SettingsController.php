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
use App\Form\User\SettingsType;
use App\Repository\UserRepository;
use App\Security\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * @Route("/user")
 */
final class SettingsController
{
    private FormFactoryInterface $form;

    private UserRepository $repository;

    private Environment $twig;

    private Security $security;

    public function __construct(Environment $twig, UserRepository $repository, FormFactoryInterface $formFactory, Security $security)
    {
        $this->form = $formFactory;
        $this->repository = $repository;
        $this->twig = $twig;
        $this->security = $security;
    }

    /**
     * @Route("/settings", methods={"GET", "POST"}, name="account_settings")
     */
    public function settings(Request $request): Response
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            $exception = new AccessDeniedException('Access Denied.');
            $exception->setAttributes(['ROLE_USER']);

            throw $exception;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $form = $this->form->create(SettingsType::class, $user);
        $form->handleRequest($request);
        $success = $form->isSubmitted() && $form->isValid();

        if ($success) {
            $this->repository->save($user);
        }

        $content = $this->twig->render('user/settings.html.twig', [
            'user' => $this->security->getUser(),
            'success' => $success,
            'form' => $form->createView(),
            'errors' => $form->getErrors(true),
        ]);

        return new Response($content);
    }
}
