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

namespace App\Tests\Controller\Api;

use App\Fixtures\UserFixture;
use App\Test\TestCase;
use Psl\Json;
use Psl\Type;

final class PingControllerTest extends TestCase
{
    protected array $fixtures = [
        UserFixture::class,
    ];

    public function testPingWithLogin(): void
    {
        $browser = $this->createAuthenticatedClient();
        $browser->request('GET', '/api/ping');

        $response = $browser->getResponse();
        $content = $response->getContent();

        $data = Json\typed($content, Type\arr(Type\string(), Type\string()));
        self::assertSame('pong!', $data['ping']);
    }

    public function testPingWithoutLogin(): void
    {
        $this->browser->request('GET', '/api/ping');

        $response = $this->browser->getResponse();

        self::assertSame(401, $response->getStatusCode());
    }
}
