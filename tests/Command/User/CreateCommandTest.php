<?php

namespace App\Tests\Command\User;

use App\Command\User\CreateCommand;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Command\AbstractCommandTest;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateCommandTest extends AbstractCommandTest
{
    /** @var string[] */
    private array $userData = [
        'email' => 'foo@example.com',
        'password' => 'foo\'s_p@ssw0rd',
    ];

    protected function setUp(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('`stty` is required to test this command.');
        }
    }

    /**
     * @dataProvider isAdminDataProvider
     *
     * This test provides all the arguments required by the command, so the
     * command runs non-interactively and won't ask for any arguments.
     */
    public function testCreateUserNonInteractive(bool $isAdmin): void
    {
        $input = $this->userData;

        if ($isAdmin) {
            $input['--super-admin'] = true;
        }

        $this->executeCommand($input);

        $this->assertUserCreated($isAdmin);
    }

    public function testCreateUserWithExistingEmail(): void
    {
        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->add($user, true);

        $commandTester = $this->executeCommand($this->userData);

        static::assertSame(1, $commandTester->getStatusCode());
    }

    /**
     * @dataProvider isAdminDataProvider
     *
     * This test doesn't provide all the arguments required by the command, so
     * the command runs interactively and will ask for values of the missing
     * arguments.
     *
     * See https://symfony.com/doc/current/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     */
    public function testCreateUserInteractive(bool $isAdmin): void
    {
        // Only 1 is passed, the rest are missing
        $arguments = $isAdmin ? ['--super-admin' => true] : [];

        // Responses given to the questions asked by the command
        $answers = array_values($this->userData);

        $this->executeCommand($arguments, $answers);

        $this->assertUserCreated($isAdmin);
    }

    /**
     * This is used to execute the same test twice: first for normal users
     * (isAdmin = false) and then for admin users (isAdmin = true).
     */
    public function isAdminDataProvider(): ?\Generator
    {
        yield [false];
        yield [true];
    }

    /**
     * This helper method checks that the user was correctly created and saved in the database.
     */
    private function assertUserCreated(bool $isAdmin): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);

        /** @var UserPasswordHasherInterface $userPasswordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = $userRepository->findOneBy(['email' => $this->userData['email']]);

        $this->assertNotNull($user);
        $this->assertSame($this->userData['email'], $user->getEmail());
        $this->assertSame($isAdmin ? ['ROLE_SUPER_ADMIN', 'ROLE_USER'] : ['ROLE_USER'], $user->getRoles());
        $this->assertTrue($passwordHasher->isPasswordValid($user, $this->userData['password']));
    }

    protected function getCommandFqcn(): string
    {
        return CreateCommand::class;
    }
}
