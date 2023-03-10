<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route(path: '/admin', name: 'admin_index', methods: [Request::METHOD_GET])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/default/index.html.twig', [
            'user_count' => $userRepository->count([]),
        ]);
    }
}
