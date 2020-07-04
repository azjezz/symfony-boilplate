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

final class SuspensionTest extends TestCase
{
    /**
     * @dataProvider provideIsActiveData
     */
    public function testIsActive(Suspension $suspension, bool $active): void
    {
        $this->assertSame($active, $suspension->isActive());
    }

    public function provideIsActiveData(): iterable
    {
        yield [Suspension::create(new User(), new DateTimeImmutable('last minute')), false];
        yield [Suspension::create(new User(), new DateTimeImmutable('yesterday')), false];
        yield [Suspension::create(new User(), new DateTimeImmutable('last week')), false];
        yield [Suspension::create(new User(), new DateTimeImmutable('last year')), false];

        yield [Suspension::create(new User(), new DateTimeImmutable('next minute')), true];
        yield [Suspension::create(new User(), new DateTimeImmutable('tomorrow')), true];
        yield [Suspension::create(new User(), new DateTimeImmutable('next week')), true];
        yield [Suspension::create(new User(), new DateTimeImmutable('next year')), true];
    }
}
