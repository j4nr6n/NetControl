<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Message\EmailVerification;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/register', name: 'register', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function register(
        Request $request,
        UserRepository $userRepository,
        MessageBusInterface $messageBus,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $user->setPassword($hashedPassword);

            $userRepository->save($user, true);
            $messageBus->dispatch(new EmailVerification((int) $user->getId()));
            $this->addFlash(
                'success',
                'Nice! You should receive an email shortly to verify your address.'
            );

            return $this->redirectToRoute('login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verify/{token}', name: 'verify_email', methods: [Request::METHOD_GET])]
    public function verifyEmail(User $user, UserRepository $userRepository): Response
    {
        $user
            ->setToken(null)
            ->setEmailVerified(true);
        $userRepository->save($user, true);

        $this->addFlash(
            'success',
            'You\'ve successfully verified your email! Go ahead and sign in!'
        );

        return $this->redirectToRoute('login');
    }

    #[Route('/{callsign}', name: 'user_profile', methods: [Request::METHOD_GET], priority: -1)]
    public function profile(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/settings/account', name: 'user_settings_account', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function accountSettings(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user, ['edit' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string|null $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

                $user->setPassword($hashedPassword);
            }

            $userRepository->save($user, true);

            return $this->redirectToRoute('user_settings_account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/settings_account.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
