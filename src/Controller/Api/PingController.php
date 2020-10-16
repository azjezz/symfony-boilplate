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

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
final class PingController
{
    /**
     * @Route("/ping", name="api_ping", methods={"GET"})
     */
    public function ping(): JsonResponse
    {
        return new JsonResponse([
            'ping' => 'pong!',
        ]);
    }
}
