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

namespace App\Fixtures;

use App\Entity\User;
use App\Service\Registration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psl\Str;

final class UserFixture extends Fixture
{
    private Registration $registration;

    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('jojo');
        $user->setEmail('jojo@example.com');
        $user->setPlainPassword('123456789');
        $this->registration->register($user);

        for ($i = 0; $i < 10; ++$i) {
            $user = new User();
            $user->setUsername(Str\format('user%d', $i));
            $user->setEmail(Str\format('user%d@example.com', $i));
            $user->setPlainPassword('password');

            $this->registration->register($user);
        }
    }
}
