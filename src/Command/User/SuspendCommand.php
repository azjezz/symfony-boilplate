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

namespace App\Command\User;

use App\Entity\Suspension;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psl;
use Psl\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SuspendCommand extends Command
{
    private EntityManagerInterface $em;

    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        parent::__construct('user:suspend');

        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    public function configure(): void
    {
        $this->setDescription('Suspend a user.')
            ->addArgument('username', InputArgument::REQUIRED, 'Unique username of the user you wish to suspend.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $username */
        $username = $input->getArgument('username');
        $user = $this->userRepository->loadUserByUsername($username);

        if ($user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_MODERATOR)) {
            $io->error('If you wish to suspend an administrator or a moderator, you would need to demote the user of their role first.');

            return 1;
        }

        if ($user->isSuspended()) {
            /** @var Collection<int, Suspension> $suspensions */
            $suspensions = $user->getSuspensions();
            /** @var Suspension $suspension */
            $suspension = $suspensions->last();
            /** @var string $reason */
            $reason = $suspension->getReason();
            /** @var DateTimeInterface $date */
            $date = $suspension->getSuspendedUntil();
            $io->warning(Str\format('"%s" is already suspended for %s until %s.', $username, $reason, $date->format('Y-m-d H:i:s')));

            return 1;
        }

        /** @var string $reason */
        $reason = $io->ask('What is the reason for this suspension', null, static function (?string $answer): ?string {
            Psl\invariant(null !== $answer && !Str\is_empty($answer), 'You must specify a reason for the suspension.');

            return $answer;
        });

        /** @var string $until */
        $until = $io->ask('When should the suspension end', null, static function (?string $date): ?string {
            if (null === $date) {
                return null;
            }

            $now = new DateTimeImmutable('now');
            $datetime = new DateTimeImmutable($date);

            Psl\invariant($datetime > $now, 'The suspension lift date must be in the future.');

            return $date;
        });

        $suspension = Suspension::create($user, new DateTimeImmutable($until), $reason);

        $this->em->persist($suspension);
        $this->em->flush();

        $io->success(Str\format('user "%s" has been suspended successfully.', $username));

        return 0;
    }
}
