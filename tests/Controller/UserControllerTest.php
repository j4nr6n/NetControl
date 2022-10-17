<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Transport\TransportInterface;

class UserControllerTest extends WebTestCase
{
    /** @var string[] */
    private array $userData = [
        'name' => 'Foo',
        'email' => 'foo@example.com',
        'password' => 'foo\'s_password',
        'callsign' => 'F4OO',
        'homepageUrl' => 'https://www.example.com',
    ];

    /**
     * @throws \Exception
     */
    public function testRegisterNewUser(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/register');

        self::assertResponseIsSuccessful();

        $client->submitForm('Register', [
            'user[email]' => $this->userData['email'],
            'user[plainPassword]' => $this->userData['password'],
        ]);

        self::assertResponseRedirects();

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $newUser = $userRepository->findOneBy(['email' => $this->userData['email']]);
        self::assertInstanceOf(User::class, $newUser);

        /** @var TransportInterface $transport */
        $transport = static::getContainer()->get('messenger.transport.async');
        self::assertCount(1, $transport->get());
    }

    /**
     * @throws \Exception
     */
    public function testShowProfile(): void
    {
        $client = static::createClient();
        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password'])
            ->setCallsign($this->userData['callsign']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        $client->request(Request::METHOD_GET, '/' . $this->userData['callsign']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $this->userData['callsign']);
    }

    /**
     * @throws \Exception
     */
    public function testGetAccountSettings(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/settings/account');

        self::assertResponseRedirects('http://localhost/login');

        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password'])
            ->setCallsign($this->userData['callsign']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/settings/account');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('aside a.active', 'Account Settings');
    }

    /**
     * @throws \Exception
     */
    public function testChangeAccountSettings(): void
    {
        $client = static::createClient();
        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/settings/account');
        $client->submitForm('Save', [
            'user[name]' => $this->userData['name'],
            'user[callsign]' => $this->userData['callsign'],
            'user[homepageUrl]' => $this->userData['homepageUrl'],
            'user[plainPassword]' => $this->userData['password'],
        ]);

        self::assertResponseRedirects();

        $user = $userRepository->findOneBy(['email' => $this->userData['email']]);
        self::assertInstanceOf(User::class, $user);
        self::assertSame($user->getName(), $this->userData['name']);
        self::assertSame($user->getCallsign(), $this->userData['callsign']);
        self::assertSame($user->getHomepageUrl(), $this->userData['homepageUrl']);
    }
}
