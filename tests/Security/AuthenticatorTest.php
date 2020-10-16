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

namespace App\Tests\Security;

use App\Entity\User;
use App\Fixtures\UserFixture;
use App\Security\Authenticator;
use App\Security\Security;
use App\Test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class AuthenticatorTest extends TestCase
{
    public array $fixtures = [
        UserFixture::class,
    ];

    public function testSupports(): void
    {
        /** @var Authenticator $authenticator */
        $authenticator = self::$container->get(Authenticator::class);

        $request = Request::create('/', 'POST');
        $request->attributes->set('_route', Authenticator::LOGIN_ROUTE);

        self::assertTrue($authenticator->supports($request));

        $request->setMethod('GET');

        self::assertFalse($authenticator->supports($request));

        $request->attributes->set('_route', Authenticator::LOGOUT_ROUTE);

        self::assertFalse($authenticator->supports($request));

        $request->setMethod('POST');

        self::assertFalse($authenticator->supports($request));
    }

    public function testGetCredentials(): void
    {
        self::bootKernel();

        /** @var Session&MockObject $session */
        $session = $this->createMock(Session::class);
        $request = Request::create('/user/login', 'POST');
        $request->setSession($session);

        /** @var Authenticator $authenticator */
        $authenticator = self::$container->get(Authenticator::class);

        $request->request->add([
            'username' => 'azjezz',
            'password' => '123456789',
            '_csrf_token' => 'secret',
        ]);

        $session->expects($this->once())->method('set')->with(Security::LAST_USERNAME, 'azjezz');

        $credentials = $authenticator->getCredentials($request);

        self::assertSame('azjezz', $credentials['username']);
        self::assertSame('123456789', $credentials['password']);
        self::assertSame('secret', $credentials['csrf_token']);

        $request->request->replace([
            'username' => ['foo', 'bar'],
            'password' => '123456789',
        ]);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $authenticator->getCredentials($request);
    }

    public function testGetUser(): void
    {
        self::bootKernel();

        /** @var Authenticator $authenticator */
        $authenticator = self::$container->get(Authenticator::class);

        $user = new User();
        /** @var UserProviderInterface&MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);
        $userProvider->expects($this->once())->method('loadUserByUsername')
            ->with('azjezz')
            ->willReturn($user);

        /** @var CsrfTokenManagerInterface $csrfManager */
        $csrfManager = self::$container->get(CsrfTokenManagerInterface::class);

        $authenticationUser = $authenticator->getUser([
            'username' => 'azjezz',
            'csrf_token' => $csrfManager->getToken(Authenticator::CSRF_TOKEN_ID)
                ->getValue(),
        ], $userProvider);

        self::assertSame($authenticationUser, $user);

        $this->expectException(InvalidCsrfTokenException::class);
        $this->expectExceptionMessage('Invalid CSRF token.');

        $authenticator->getUser(['username' => 'jojo', 'csrf_token' => 'invalid'], $userProvider);
    }

    public function testCheckCredentials(): void
    {
        self::bootKernel();

        $authenticator = self::$container->get(Authenticator::class);
        $provider = self::$container->get(UserProviderInterface::class);

        $user = $provider->loadUserByUsername('jojo');

        self::assertTrue($authenticator->checkCredentials(['password' => '123456789'], $user));
        self::assertFalse($authenticator->checkCredentials(['password' => 'foo'], $user));
    }
}
