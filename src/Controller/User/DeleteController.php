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
use App\Form\User\DeleteType;
use App\Repository\UserRepository;
use App\Security\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * @Route("/user")
 */
final class DeleteController
{
    private UserRepository $repository;

    private FormFactoryInterface $form;

    private Security $security;

    private Environment $twig;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(Environment $twig, UserRepository $repository, FormFactoryInterface $form, Security $security, UrlGeneratorInterface $urlGenerator)
    {
        $this->repository = $repository;
        $this->form = $form;
        $this->security = $security;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/delete", methods={"POST", "GET"}, name="user_delete")
     */
    public function delete(Request $request): Response
    {
        if (!$this->security->isGranted(User::ROLE_USER)) {
            $exception = new AccessDeniedException('Access Denied.');
            $exception->setAttributes([User::ROLE_USER]);

            throw $exception;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $form = $this->form->create(DeleteType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * Delete the user from the database.
             */
            $this->repository->delete($user);

            /**
             * Erase plain text password stored temporarily in the user instance.
             */
            $user->eraseCredentials();

            /**
             * Force logout the user.
             */
            $this->security->invalidate($request->getSession());

            /**
             * Redirect the user back to the login page.
             */
            $url = $this->urlGenerator->generate('user_login');

            return new RedirectResponse($url);
        }

        $content = $this->twig->render('user/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'errors' => $form->getErrors(),
        ]);

        return new Response($content);
    }
}
