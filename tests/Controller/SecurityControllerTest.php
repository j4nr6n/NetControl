<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    /** @var string[] */
    private array $userData = [
        'email' => 'foo@example.com',
        'password' => 'foo\'s_password',
    ];

    /**
     * @throws \Exception
     */
    public function testLogin(): void
    {
        $client = static::createClient();

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setEmailVerified(true);
        $user->setPassword($passwordHasher->hashPassword($user, $this->userData['password']));

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        $client->request(Request::METHOD_GET, '/login');
        self::assertResponseIsSuccessful();

        $client->submitForm('Sign In', [
            '_username' => $this->userData['email'],
            '_password' => $this->userData['password'],
        ]);

        self::assertResponseRedirects('http://localhost/');
    }

    public function testEmailVerification(): void
    {
        $client = static::createClient();

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())->setEmail($this->userData['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $this->userData['password']));

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        $client->request(Request::METHOD_GET, '/login');
        self::assertResponseIsSuccessful();

        // Must verify email before logging in
        $client->submitForm('Sign In', [
            '_username' => $this->userData['email'],
            '_password' => $this->userData['password'],
        ]);
        self::assertResponseRedirects('http://localhost/login');

        /** @var User $user */
        $user = $userRepository->find($user->getId());
        $user->setEmailVerified(true);
        $userRepository->save($user, true);

        $client->request(Request::METHOD_GET, '/login');
        $client->submitForm('Sign In', [
            '_username' => $this->userData['email'],
            '_password' => $this->userData['password'],
        ]);
        self::assertResponseRedirects('http://localhost/');
    }

    /**
     * @throws \Exception
     */
    public function testLogout(): void
    {
        $client = static::createClient();
        $user = (new User())
            ->setEmail($this->userData['email'])
            ->setPassword($this->userData['password']);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/logout');

        self::assertResponseRedirects('http://localhost/');
    }
}
