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

namespace App\Exception;

use App\Entity\Suspension;
use App\Entity\User;
use Psl\Str;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

final class UserSuspendedException extends AccountStatusException
{
    private const Message = 'user.suspended';

    private Suspension $suspension;

    public function __serialize(): array
    {
        return [$this->suspension, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$this->suspension, $data] = $data;

        parent::__unserialize($data);
    }

    public static function create(User $user, Suspension $suspension): UserSuspendedException
    {
        $self = new self(Str\format('%s is suspended.', $user->getUsername()));
        $self->setUser($user);
        $self->suspension = $suspension;

        return $self;
    }

    public function getSuspension(): Suspension
    {
        return $this->suspension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return self::Message;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageData(): array
    {
        return [
            'suspension_reason' => $this->suspension->getReason(),
            'suspended_until' => $this->suspension->getSuspendedUntil()->format('Y-m-d H:i:s'),
        ];
    }
}
