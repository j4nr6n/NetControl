<?php

namespace App\Tests\Command\User;

use App\Command\User\PromoteCommand;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Command\AbstractCommandTest;

class PromoteCommandTest extends AbstractCommandTest
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
     */
    protected function testPromoteUser(bool $isAdmin): void
    {
        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        $input = ['email' => $this->userData['email']];

        if ($isAdmin) {
            $input['--super-admin'] = true;
        }

        $commandTester = $this->executeCommand($input);

        static::assertSame(1, $commandTester->getStatusCode());

        /** @var User $user */
        $user = $userRepository->find($user->getId());
        self::assertContains('ROLE_SUPER_ADMIN', $user->getRoles());
    }

    /**
     * @dataProvider isAdminDataProvider
     */
    public function testPromoteNonExistingUser(bool $isAdmin): void
    {
        $input = ['email' => $this->userData['email']];

        if ($isAdmin) {
            $input['--super-admin'] = true;
        }

        $commandTester = $this->executeCommand($input);

        self::assertSame(1, $commandTester->getStatusCode());
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

    protected function getCommandFqcn(): string
    {
        return PromoteCommand::class;
    }
}
