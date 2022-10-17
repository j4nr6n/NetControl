<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends WebTestCase
{
    /** @var string[] */
    private array $adminData = [
        'email' => 'admin@example.com',
        'password' => 'admin\'s_password',
    ];

    /** @var string[] */
    private array $userData = [
        'email' => 'foo@example.com',
        'password' => 'foo\'s_password',
    ];

    public function testGetIndex(): void
    {
        $client = static::createClient();
        $admin = (new User())
            ->setEmail($this->adminData['email'])
            ->setPassword($this->adminData['password'])
            ->setRoles(['ROLE_SUPER_ADMIN']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($admin, true);

        $client->request(Request::METHOD_GET, '/admin/user');
        self::assertResponseRedirects('http://localhost/login');

        $client->loginUser($admin);
        $client->request(Request::METHOD_GET, '/admin/user');
        self::assertResponseIsSuccessful();
    }

    public function testCreateUser(): void
    {
        $client = static::createClient();
        $admin = (new User())
            ->setEmail($this->adminData['email'])
            ->setPassword($this->adminData['password'])
            ->setRoles(['ROLE_SUPER_ADMIN']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($admin, true);

        $client->request(Request::METHOD_GET, '/admin/user/new');
        self::assertResponseRedirects('http://localhost/login');

        $client->loginUser($admin);
        $client->request(Request::METHOD_GET, '/admin/user/new');
        self::assertResponseIsSuccessful();

        $client->submitForm('Save', [
            'user[email]' => $this->userData['email'],
            'user[plainPassword]' => $this->userData['password'],
        ]);
        self::assertResponseRedirects();
    }

    public function testEditUser(): void
    {
        $client = static::createClient();
        $admin = (new User())
            ->setEmail($this->adminData['email'])
            ->setPassword($this->adminData['password'])
            ->setRoles(['ROLE_SUPER_ADMIN']);
        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($admin, true);
        $userRepository->save($user, true);

        $client->request(Request::METHOD_GET, sprintf('/admin/user/%d/edit', (int) $user->getId()));
        self::assertResponseRedirects('http://localhost/login');

        $client->loginUser($admin);
        $client->request(Request::METHOD_GET, sprintf('/admin/user/%d/edit', (int) $user->getId()));
        self::assertResponseIsSuccessful();

        $client->submitForm('Save', [
            'user[email]' => $this->userData['email'],
            'user[callsign]' => 'F0O',
        ]);
        self::assertResponseRedirects(sprintf('/admin/user/%d', (int) $user->getId()));
    }

    public function testDeleteUser(): void
    {
        $client = static::createClient();
        $admin = (new User())
            ->setEmail($this->adminData['email'])
            ->setPassword($this->adminData['password'])
            ->setRoles(['ROLE_SUPER_ADMIN']);
        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($admin, true);
        $userRepository->save($user, true);

        $client->request(Request::METHOD_GET, sprintf('/admin/user/%d', (int) $user->getId()));
        self::assertResponseRedirects('http://localhost/login');

        $client->loginUser($admin);
        $client->request(Request::METHOD_GET, sprintf('/admin/user/%d', (int) $user->getId()));
        self::assertResponseIsSuccessful();

        $client->submitForm('Delete');
        self::assertResponseRedirects('/admin/user');
    }
}
