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

use App\Entity\Suspension;
use App\Entity\User;
use App\Exception\UserSuspendedException;
use App\Security\UserChecker;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class UserCheckerTest extends TestCase
{
    /**
     * @dataProvider provideCheckPreAuthData
     */
    public function testCheckPreAuth(User $user): void
    {
        $checker = new UserChecker();

        if ($user->isSuspended()) {
            $this->expectException(UserSuspendedException::class);
            $this->expectExceptionMessage(Str\format('%s is suspended.', $user->getUsername()));
        } else {
            // Test needs to perform assertions.
            $this->addToAssertionCount(1);
        }

        $checker->checkPreAuth($user);
    }

    public function provideCheckPreAuthData(): iterable
    {
        // Not suspended.
        yield [new User()];

        // Previously suspended.
        $user = new User();
        Suspension::create($user, new DateTimeImmutable('last year'));
        yield [$user];

        // Previously suspended multiple times.
        $user = new User();
        Suspension::create($user, new DateTimeImmutable('last week'));
        Suspension::create($user, new DateTimeImmutable('last month'));
        Suspension::create($user, new DateTimeImmutable('last year'));
        yield [$user];

        // Currently suspended with previous suspensions.
        $user = new User();
        Suspension::create($user, new DateTimeImmutable('last week'));
        Suspension::create($user, new DateTimeImmutable('last month'));
        Suspension::create($user, new DateTimeImmutable('last year'));
        Suspension::create($user, new DateTimeImmutable('next year'));
        yield [$user];

        // Currently suspended with no previous suspensions.
        $user = new User();
        Suspension::create($user, new DateTimeImmutable('next year'));
        yield [$user];
    }
}
