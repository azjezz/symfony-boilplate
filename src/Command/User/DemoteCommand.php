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

use App\Entity\User;
use App\Repository\UserRepository;
use Psl\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DemoteCommand extends Command
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct('user:demote');

        $this->userRepository = $userRepository;
    }

    public function configure(): void
    {
        $this->setDescription('Demote an admin or moderator to regular user role')
            ->addArgument('username', InputArgument::REQUIRED, 'Unique username of the admin/moderator you wish to demote.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $username */
        $username = $input->getArgument('username');
        $user = $this->userRepository->loadUserByUsername($username);

        $isAdmin = $user->hasRole(User::ROLE_ADMIN);

        if (
            $isAdmin &&
            !$io->confirm(Str\format('Are you sure you want to demote admin "%s" to user role?', $user->getUsername()))
        ) {
            return 1;
        }

        if (!$isAdmin && !$user->hasRole(User::ROLE_MODERATOR)) {
            $io->warning(Str\format('"%s" is already a regular user.', $username));

            return 1;
        }

        $user->setRoles([User::ROLE_USER]);
        $this->userRepository->save($user);

        $io->success(Str\format('%s "%s" has been demoted to regular user.', $isAdmin ? 'admin' : 'moderator', $username));

        return 0;
    }
}
