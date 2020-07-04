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

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Psl;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ResetPasswordRequestRepository")
 */
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use Behavior\Identifiable;
    use ResetPasswordRequestTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private ?User $user = null;

    public function __construct(User $user, DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    /**
     * @return User
     */
    public function getUser(): object
    {
        $user = $this->user;
        Psl\invariant(null !== $user, 'reset password request has not been initialized yet.');

        /** @var User $user */
        return $user;
    }
}
