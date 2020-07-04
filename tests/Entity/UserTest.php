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

namespace App\Tests\Entity;

use App\Entity\Suspension;
use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;

final class UserTest extends TestCase
{
    public function testAddSuspension(): void
    {
        $user = new User();

        $previousSuspension = Suspension::create($user, new DateTimeImmutable('last week'));
        $currentSuspension = Suspension::create($user, new DateTimeImmutable('next week'));

        $suspensions = $user->getSuspensions();
        $this->assertTrue($suspensions->contains($previousSuspension));
        $this->assertTrue($suspensions->contains($currentSuspension));

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Unable to suspend an already suspended user.');

        Suspension::create($user, new DateTimeImmutable('tomorrow'));
    }
}
