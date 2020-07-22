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

namespace App\Service;

use App\Entity\User;
use App\Form\User\PasswordReset\PasswordResetType;
use App\Mail\PasswordResetEmail;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class PasswordReset
{
    public const RESET_PASSWORD_PUBLIC_TOKEN_ID = 'ResetPasswordPublicToken';

    public const RESET_PASSWORD_CHECK_EMAIL_ID = 'ResetPasswordCheckEmail';

    public const RESET_PASSWORD_ERROR = 'reset_password_error';

    public ResetPasswordHelperInterface $helper;

    private Mailer $mailer;

    private UrlGeneratorInterface $urlGenerator;

    private UserRepository $repository;

    private UserPasswordEncoderInterface $encoder;

    public function __construct(ResetPasswordHelperInterface $helper, Mailer $mailer, UrlGeneratorInterface $urlGenerator, UserRepository $repository, UserPasswordEncoderInterface $encoder)
    {
        $this->helper = $helper;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->repository = $repository;
        $this->encoder = $encoder;
    }

    public function sendPasswordResetEmail(Session $session, string $address): RedirectResponse
    {
        $user = $this->repository->findOneBy([
            'email' => $address,
        ]);

        $session->set(self::RESET_PASSWORD_CHECK_EMAIL_ID, true);

        if (null === $user || !$user->isPasswordResetEnabled()) {
            $url = $this->urlGenerator->generate('user_password_reset_confirm');

            return new RedirectResponse($url);
        }

        try {
            $resetToken = $this->helper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $exception) {
            $session->getFlashBag()->add(self::RESET_PASSWORD_ERROR, $exception->getReason());

            return new RedirectResponse($this->urlGenerator->generate('user_password_reset_request'));
        }

        $email = PasswordResetEmail::create($user, [
            'resetToken' => $resetToken,
            'tokenLifetime' => $this->helper->getTokenLifetime(),
        ]);

        $this->mailer->send($email);

        return new RedirectResponse($this->urlGenerator->generate('user_password_reset_confirm'));
    }

    public function canCheckEmail(SessionInterface $session): bool
    {
        return $session->has(self::RESET_PASSWORD_CHECK_EMAIL_ID);
    }

    public function storeTokenInSession(SessionInterface $session, string $token): RedirectResponse
    {
        // We store the token in session and remove it from the URL, to avoid the URL being
        // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
        $session->set(self::RESET_PASSWORD_PUBLIC_TOKEN_ID, $token);

        $url = $this->urlGenerator->generate('user_password_reset');

        return new RedirectResponse($url);
    }

    public function getTokenFromSession(SessionInterface $session): string
    {
        /** @var string|null $token */
        $token = $session->get(self::RESET_PASSWORD_PUBLIC_TOKEN_ID);

        if (null === $token) {
            throw new NotFoundHttpException('No reset password token found in the URL or in the session.');
        }

        return $token;
    }

    /**
     * @throws ResetPasswordExceptionInterface
     */
    public function retrieveUser(string $token): User
    {
        /** @var User $user */
        $user = $this->helper->validateTokenAndFetchUser($token);

        if (!$user->isPasswordResetEnabled()) {
            throw new InvalidResetPasswordTokenException();
        }

        return $user;
    }

    public function resetPassword(Request $request, FormInterface $form, User $user, string $token): void
    {
        /** @var string $password */
        $password = $form->get(PasswordResetType::PasswordField)->getData();

        // A password reset token should be used only once, remove it.
        $this->helper->removeResetRequest($token);

        // Encode the plain password, and set it.
        $encodedPassword = $this->encoder->encodePassword($user, $password);

        $this->repository->upgradePassword($user, $encodedPassword);

        // The session is cleaned up after the password has been changed.
        $this->cleanSessionAfterReset($request->getSession());
    }

    private function cleanSessionAfterReset(SessionInterface $session): void
    {
        $session->remove(self::RESET_PASSWORD_PUBLIC_TOKEN_ID);
        $session->remove(self::RESET_PASSWORD_CHECK_EMAIL_ID);
    }
}
