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

namespace App\Security;

use Stringable;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;

interface UserInterface extends SymfonyUser, Stringable, EquatableInterface
{
    public const ROLE_USER = 'ROLE_USER';

    public const ROLE_MODERATOR = 'ROLE_MOD';

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const ROLES = [
        self::ROLE_USER,
        self::ROLE_MODERATOR,
        self::ROLE_ADMIN,
    ];

    public function __serialize(): array;

    public function __unserialize(array $data): void;

    public function __toString(): string;
}
