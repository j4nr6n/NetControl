<?php

namespace App\Controller\Admin;

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

#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('', name: 'admin_user_index', methods: [Request::METHOD_GET])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $template = $request->query->get('ajax')
            ? '_list.html.twig'
            : 'index.html.twig';

        return $this->render('admin/user/' . $template, [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_user_new', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function new(
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

            if ($request->query->get('ajax')) {
                return new Response(null, Response::HTTP_NO_CONTENT);
            }

            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        $template = $request->query->get('ajax')
            ? 'user/_form.html.twig'
            : 'admin/user/new.html.twig';

        return $this->render($template, [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_show', methods: [Request::METHOD_GET])]
    public function show(Request $request, User $user): Response
    {
        $template = $request->query->get('ajax')
            ? '_show.html.twig'
            : 'show.html.twig';

        return $this->render('admin/user/' . $template, [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(
        Request $request,
        User $user,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
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

            if ($request->query->get('ajax')) {
                return new Response(null, Response::HTTP_NO_CONTENT);
            }

            return $this->redirectToRoute('admin_user_show', [
                'id' => $user->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        $template = $request->query->get('ajax')
            ? 'user/_form.html.twig'
            : 'admin/user/edit.html.twig';

        return $this->render($template, [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_delete', methods: [Request::METHOD_POST, Request::METHOD_DELETE])]
    public function delete(
        Request $request,
        User $user,
        UserRepository $userRepository
    ): Response {
        $tokenId = sprintf('delete_user_%s', (int) $user->getId());
        $token = (string) $request->request->get('_token');

        if ($this->isCsrfTokenValid($tokenId, $token)) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
