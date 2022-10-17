<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class DefaultControllerTest extends WebTestCase
{
    /** @var string[] */
    private array $userData = [
        'email' => 'foo@example.com',
        'password' => 'foo\'s_password',
    ];

    public function testGetIndex(): void
    {
        $client = static::createClient();
        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password'])
            ->setRoles(['ROLE_SUPER_ADMIN']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        $client->request(Request::METHOD_GET, '/admin');
        self::assertResponseRedirects('http://localhost/login');

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/admin');
        self::assertResponseIsSuccessful();
    }
}
