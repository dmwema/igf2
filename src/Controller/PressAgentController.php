<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PressAgentController extends AbstractController
{
    #[Route('/press/agent', name: 'app_press_agent')]
    public function index(): Response
    {
        return $this->render('press_agent/index.html.twig', [
            'controller_name' => 'PressAgentController',
        ]);
    }
}
