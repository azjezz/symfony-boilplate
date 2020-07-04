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

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
final class LogoutController
{
    /**
     * @Route("/logout", methods={"GET", "POST"}, name="user_logout")
     */
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall.
    }
}
