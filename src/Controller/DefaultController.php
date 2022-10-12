<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('', name: 'homepage', methods: [Request::METHOD_GET])]
    public function homepage(): Response
    {
        if ($this->getUser()) {
            // TODO: Move this to a separate controller method
            return $this->render('default/dashboard.html.twig');
        }

        return $this->render('default/homepage.html.twig');
    }
}
