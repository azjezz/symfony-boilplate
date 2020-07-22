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

use App\Security\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Psl;
use Psl\Arr;
use Psl\Iter;
use Psl\Str;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(
 *     fields={"username"},
 *     message="registration.username.used",
 *     groups={"registration"}
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     message="registration.email.used",
 *     groups={"registration"}
 * )
 */
class User implements UserInterface
{
    use Behavior\Identifiable;
    use Behavior\Timestamp;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     *
     * @Assert\Length(
     *     min="2",
     *     max="32",
     *     minMessage="registration.username.short",
     *     maxMessage="registration.username.long",
     *     groups={"registration"}
     * )
     * @Assert\Regex(
     *     pattern     = "/^[a-z]+$/i",
     *     htmlPattern = "^[a-zA-Z]+$",
     *     message="registration.username.pattern",
     *     groups={"registration"}
     * )
     */
    private ?string $username = null;

    /**
     * @ORM\Column(type="string", length=300, unique=true)
     *
     * @Assert\Email(
     *     message="registration.email.invalid",
     *     groups={"registration", "settings"}
     * )
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="array")
     *
     * @var string[]
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    private ?string $password = null;

    /**
     * @Assert\NotBlank(
     *     message="registration.password.blank",
     *     groups={"registration"}
     * )
     * @Assert\Length(
     *     charset="UTF-8",
     *     min=8,
     *     max=4069,
     *     minMessage="registration.password.short",
     *     maxMessage="registration.password.long",
     *     charsetMessage="registration.password.charset",
     *     groups={"registration"}
     * )
     * @UserPassword(
     *     message="delete.password",
     *     groups={"delete"}
     * )
     */
    private ?string $plainPassword = null;

    /**
     * Is the password reset feature enabled for this user?
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $passwordResetEnabled = true;

    /**
     * @var Collection<int, Suspension>
     *
     * @ORM\OneToMany(targetEntity="Suspension", mappedBy="user", orphanRemoval=true)
     */
    private ?Collection $suspensions = null;

    public function __construct()
    {
        $this->suspensions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->username ?? $this->email ?? '(unknown)';
    }

    /**
     * @return array{id: int|null, username: null|string, email: null|string, password: null|string, roles: array<array-key, string>, password_reset_enabled: bool}
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'roles' => $this->roles,
            'password_reset_enabled' => $this->passwordResetEnabled,
        ];
    }

    public function __unserialize(array $data): void
    {
        /** @var array{id: int|null, username: null|string, email: null|string, password: null|string, roles: array<array-key, string>, password_reset_enabled: bool} $data */
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->roles = $data['roles'];
        $this->password = $data['password'];
        $this->passwordResetEnabled = $data['password_reset_enabled'];
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->username ?? '';
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        /** @var string[] */
        return Arr\unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        /** @var string[] $mapped */
        $mapped = Iter\map(
            $roles,
            fn (string $role): string => Str\uppercase($role)
        );

        /** @var array<int, string> $roles */
        $roles = Arr\values(Arr\unique($mapped));
        $this->roles = $roles;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return Arr\contains($this->getRoles(), $role);
    }

    public function addRole(string $role): self
    {
        if ($this->hasRole($role)) {
            return $this;
        }

        $roles = $this->getRoles();
        $roles[] = $role;
        $this->setRoles($roles);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * This method is only used during the registration process.
     *
     * Plain password is *never* stored.
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): void
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function isPasswordResetEnabled(): bool
    {
        return $this->passwordResetEnabled;
    }

    public function setPasswordResetEnabled(bool $passwordResetEnabled): self
    {
        $this->passwordResetEnabled = $passwordResetEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(SymfonyUser $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($user->getId() !== $this->getId()) {
            return false;
        }

        if ($user->getUsername() !== $this->getUsername()) {
            return false;
        }

        if ($user->getEmail() !== $this->getEmail()) {
            return false;
        }

        if ($user->getPassword() !== $this->getPassword()) {
            return false;
        }

        if ($user->getRoles() !== $this->getRoles()) {
            return false;
        }

        if ($user->isPasswordResetEnabled() !== $this->isPasswordResetEnabled()) {
            return false;
        }

        return true;
    }

    public function isSuspended(): bool
    {
        /** @var Collection<int, Suspension>|null $suspensions */
        $suspensions = $this->getSuspensions();

        if (null === $suspensions) {
            return false;
        }

        $suspension = $suspensions->last();

        if (!$suspension) {
            return false;
        }

        return $suspension->isActive();
    }

    /**
     * @return Collection<int, Suspension>|null
     */
    public function getSuspensions(): ?Collection
    {
        return $this->suspensions;
    }

    public function addSuspension(Suspension $suspension): self
    {
        Psl\invariant(!$this->isSuspended(), 'Unable to suspend an already suspended user.');

        if (null !== $this->suspensions && !$this->suspensions->contains($suspension)) {
            $this->suspensions[] = $suspension;
            $suspension->setUser($this);
        }

        return $this;
    }

    public function removeSuspension(Suspension $suspension): self
    {
        if (null !== $this->suspensions && $this->suspensions->contains($suspension)) {
            $this->suspensions->removeElement($suspension);
            // set the owning side to null (unless already changed)
            if ($suspension->getUser() === $this) {
                $suspension->setUser(null);
            }
        }

        return $this;
    }
}
