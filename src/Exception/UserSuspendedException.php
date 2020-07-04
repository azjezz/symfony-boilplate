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
use DateTimeInterface;
use Psl\Str;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

final class UserSuspendedException extends AccountStatusException
{
    private const Message = 'user.suspended';

    private Suspension $suspension;

    /**
     * @return array{0: Suspension, 1: array<array-key, mixed>}
     */
    public function __serialize(): array
    {
        /** @var array<array-key, mixed> $data */
        $data = parent::__serialize();

        return [$this->suspension, $data];
    }

    public function __unserialize(array $data): void
    {
        /**
         * @var array{0:         Suspension, 1: array<array-key, mixed>} $data
         * @var array<array-key, mixed> $parent
         */
        [$this->suspension, $parent] = $data;

        parent::__unserialize($parent);
    }

    public static function create(User $user, Suspension $suspension): UserSuspendedException
    {
        $self = new self(Str\format('%s is suspended.', $user->getUsername()));
        $self->setUser($user);
        $self->suspension = $suspension;

        return $self;
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
        $reason = $this->suspension->getReason();
        /** @var DateTimeInterface $date */
        $date = $this->suspension->getSuspendedUntil();

        return [
            'suspension_reason' => $reason,
            'suspended_until' => $date->format('Y-m-d H:i:s'),
        ];
    }
}
