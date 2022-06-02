<?php

namespace App\Controller;

use App\Entity\Press;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PressController extends AbstractController
{
    #[Route('/press', name: 'press')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $presses = $doctrine->getRepository(Press::class)->findAll();
        return $this->render('press/index.html.twig', [
            "presses" => $presses
        ]);
    }
}
