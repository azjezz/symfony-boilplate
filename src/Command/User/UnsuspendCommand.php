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
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psl\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UnsuspendCommand extends Command
{
    private EntityManagerInterface $em;

    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        parent::__construct('user:unsuspend');

        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    public function configure(): void
    {
        $this->setDescription('Unsuspend a user.')
            ->addArgument('username', InputArgument::REQUIRED, 'Unique username of the user you wish to unsuspend.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $username */
        $username = $input->getArgument('username');
        $user = $this->userRepository->loadUserByUsername($username);

        if (!$user->isSuspended()) {
            $io->warning(Str\format('"%s" is not suspended.', $username));

            return 1;
        }

        /** @var Collection<int, Suspension> $suspensions */
        $suspensions = $user->getSuspensions();
        /** @var Suspension $suspension */
        $suspension = $suspensions->last();
        $suspension->setSuspendedUntil(new DateTimeImmutable('now'));

        $this->em->flush();

        $io->success(Str\format('user "%s" has been unsuspended successfully.', $username));

        return 0;
    }
}
