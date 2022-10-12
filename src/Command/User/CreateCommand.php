<?php

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand('app:user:create', 'Creates a User')]
class CreateCommand extends Command
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private SymfonyStyle $io;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp($this->getCommandHelp())
            ->addArgument(
                'email',
                InputArgument::OPTIONAL,
                'Email address for the new user'
            )
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                'Password to assign to the new user'
            )
            ->addOption(
                'super-admin',
                null,
                InputOption::VALUE_NONE,
                'Grant the user SUPER_ADMIN permissions'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (!in_array(null, [$input->getArgument('email'), $input->getArgument('password')], true)) {
            return;
        }

        /** @var string|null $email */
        $email = $input->getArgument('email');
        if ($email !== null) {
            $this->io->text('> <info>Email</info>: ' . $email);
        } else {
            /** @var string $email */
            $email = $this->io->ask('Email', null, static function (?string $answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('An email address is required.');
                }

                return $answer;
            });

            $input->setArgument('email', $email);
        }

        /** @var string|null $password */
        $password = $input->getArgument('password');
        if ($password !== null) {
            $this->io->text('> <info>Password</info>: ' . $password);
        } else {
            /** @var string $password */
            $password = $this->io->askHidden('Password', static function (?string $answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('A password is required.');
                }

                return $answer;
            });

            $input->setArgument('password', $password);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $input->getArgument('email');
        $password = (string) $input->getArgument('password');

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error('A user already exists with that email');

            return Command::FAILURE;
        }

        $user = (new User())->setEmail($email);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        if ($input->getOption('super-admin')) {
            $user->setRoles(['ROLE_SUPER_ADMIN']);
        }

        $this->userRepository->add($user, true);

        $io->success('Done!');

        return Command::SUCCESS;
    }

    /**
     * The command help is usually included in the `configure()` method, but when
     * it's too long, it's better to define a separate method to maintain the
     * code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
            The <info>%command.name%</info> command creates new users and saves them in the database:

              <info>%command.full_name%</info> <comment>email password</comment>

            By default the command creates regular users. To create administrator
            users, add the <comment>--super-admin</comment> option:

              <info>%command.full_name%</info> email password <comment>--super-admin</comment>
            HELP;
    }
}
