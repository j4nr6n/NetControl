<?php

namespace App\Tests\Controller;

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

    public function testGetHomepage(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Homepage');
    }

    public function testGetDashboard(): void
    {
        $client = static::createClient();
        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->add($user, true);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Dashboard');
    }
}
