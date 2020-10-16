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

namespace App\Test;

use Liip\TestFixturesBundle\Test\FixturesTrait;
use Psl;
use Psl\Json;
use Psl\Str;
use Psl\Type;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TestCase extends WebTestCase
{
    use FixturesTrait;

    protected array $fixtures = [];

    protected array $options = [];

    protected KernelBrowser $browser;

    protected function setUp(): void
    {
        $this->browser = static::createClient($this->options);
        $this->loadFixtures($this->fixtures, false);
    }

    /**
     * Create a client with a default Authorization header.
     */
    protected function createAuthenticatedClient(string $username = 'jojo', string $password = '123456789'): KernelBrowser
    {
        $this->browser->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], Json\encode([
            'username' => $username,
            'password' => $password,
        ]));

        $response = $this->browser->getResponse();

        Psl\invariant($response->isSuccessful(), 'Invalid credentials.');

        $data = Json\typed($response->getContent(), Type\arr(Type\string(), Type\string()));

        $this->browser->setServerParameter('HTTP_Authorization', Str\format('Bearer %s', $data['token']));

        return $this->browser;
    }
}
