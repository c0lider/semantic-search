<?php

declare(strict_types=1);

namespace App\Command;

use Pimcore\Model\User;
use Pimcore\Tool\Authentication;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create_user',
    description: 'Create a user'
)]
class CreateUserCommand extends Command
{
    private const string USERNAME_OPTION = 'username';
    private const string PASSWORD_OPTION = 'password';
    private const string ADMIN_OPTION = 'admin';
    protected function configure()
    {
        $this
            ->addOption(self::USERNAME_OPTION, 'u', InputOption::VALUE_REQUIRED, 'The username')
            ->addOption(self::PASSWORD_OPTION, 'p', InputOption::VALUE_REQUIRED, 'The password')
            ->addOption(self::ADMIN_OPTION, 'a', InputOption::VALUE_NONE, 'Whether the user is an admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption(self::USERNAME_OPTION)) {
            $output->writeln('<error>Username is required</error>');
            return self::INVALID;
        }

        if (!$input->getOption(self::PASSWORD_OPTION)) {
            $output->writeln('<error>Password is required</error>');
            return self::INVALID;
        }

        $username = $input->getOption(self::USERNAME_OPTION);
        $password = $input->getOption(self::PASSWORD_OPTION);
        $isAdmin = $input->getOption(self::ADMIN_OPTION);

        $user = User::getByName($username);

        if ($user) {
            $output->writeln('<error>User already exists</error>');
            return self::FAILURE;
        }

        $passwordHash = Authentication::getPasswordHash($username, $password);

        $user = User::create([
            'parentId' => 0,
            'username' => $username,
            'password' => $passwordHash,
            'admin' => $isAdmin,
            'hasCredentials' => true,
            'active' => true,
        ]);

        try {
            $user->save();
        } catch (\Exception $e) {
            $output->writeln('<error>Error saving user: '.$e->getMessage().'</error>');
            return self::FAILURE;
        }

        $output->writeln('<info>User created successfully</info>');

        return self::SUCCESS;
    }
}
