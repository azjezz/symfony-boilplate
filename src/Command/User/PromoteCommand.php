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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PromoteCommand extends Command
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct('user:promote');

        $this->userRepository = $userRepository;
    }

    public function configure(): void
    {
        $this->setDescription('Promote user to moderator or admin role')
            ->addArgument('username', InputArgument::REQUIRED, 'Unique username of the user you wish to promote.')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'If this option is specified, the user will be promoted to admin role.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $username */
        $username = $input->getArgument('username');
        $user = $this->userRepository->loadUserByUsername($username);

        $admin = $input->getOption('admin');

        if (
            $admin &&
            !$io->confirm(Str\format('Are you sure you want to promote "%s" to admin role?', $user->getUsername()))
        ) {
            return 1;
        }

        $role = $admin ? User::ROLE_ADMIN : User::ROLE_MODERATOR;

        if ($user->hasRole($role)) {
            $io->warning(Str\format('User "%s" is already %s.', $username, $admin ? 'an admin' : 'a moderator'));

            return 1;
        }

        $user->addRole($role);
        $this->userRepository->save($user);

        $io->success(Str\format('User "%s" has been promoted to %s role.', $username, $admin ? 'admin' : 'moderator'));

        return 0;
    }
}
