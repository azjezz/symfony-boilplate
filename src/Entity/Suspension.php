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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SuspensionRepository")
 *
 * @ORM\HasLifecycleCallbacks
 */
class Suspension
{
    use Behavior\Timestamp;
    use Behavior\Identifiable;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="suspensions")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @Assert\GreaterThan("today UTC", message="user.suspension.suspended_until.past")
     */
    private ?DateTimeInterface $suspendedUntil = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $reason = null;

    public static function create(User $user, DateTimeInterface $until, ?string $reason = null): Suspension
    {
        $suspension = new self();
        $suspension->setUser($user);
        $suspension->setSuspendedUntil($until);
        $suspension->setReason($reason);

        $user->addSuspension($suspension);

        return $suspension;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSuspendedUntil(): ?DateTimeInterface
    {
        return $this->suspendedUntil;
    }

    public function setSuspendedUntil(?DateTimeInterface $suspendedUntil): self
    {
        $this->suspendedUntil = $suspendedUntil;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function isActive(): bool
    {
        return self::getCurrentDateTime() < $this->getSuspendedUntil();
    }
}
