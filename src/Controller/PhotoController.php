<?php

namespace App\Controller;

use App\Entity\Photo;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    /**
     * @Route("/photos", name="photos")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $photos = $doctrine->getRepository(Photo::class)->findAll();

        return $this->render('photo/index.html.twig', [
            'photos' => $photos
        ]);
    }
}
