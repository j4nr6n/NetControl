<?php

namespace App\Command\User;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:user:promote', 'Promotes a User')]
class PromoteCommand extends Command
{
    public function __construct(
        readonly private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Email address of the user to promote',
            )
            ->addOption(
                'super-admin',
                null,
                InputOption::VALUE_NONE,
                'Grant the user SUPER_ADMIN permissions'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $input->getArgument('email');
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error('There are no users with that email.');

            return Command::FAILURE;
        }

        if ($input->getOption('super-admin')) {
            $user->setRoles(['ROLE_SUPER_ADMIN']);
        }

        $this->userRepository->save($user, true);

        $io->success('Done!');

        return Command::SUCCESS;
    }
}
