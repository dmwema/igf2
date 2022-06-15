<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Rapport;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $posts = $doctrine->getRepository(Post::class)->findAll();
        return $this->render('welcome/index.html.twig', [
            'controller_name' => 'WelcomeController',
            'posts' => array_slice($posts, 0, 5)
        ]);
    }
    /**
     * @Route("/aze", name="aze")
     */
    public function aze(ManagerRegistry $doctrine): Response
    {
        $rapport = $doctrine->getRepository(Rapport::class)->find(1);
        return $this->render(
            'mail/index.html.twig',
            ['rapport' => $rapport]
        );
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(): Response
    {
        return $this->render('contact.html.twig', [
            'controller_name' => 'WelcomeController',
        ]);
    }

    /**
     * @Route("/attributions", name="attributions")
     */
    public function attributions(): Response
    {
        return $this->render('attributions.html.twig', [
            'controller_name' => 'WelcomeController',
        ]);
    }

    /**
     * @Route("/historique", name="historique")
     */
    public function historique(): Response
    {
        return $this->render('historique.html.twig', [
            'controller_name' => 'WelcomeController',
        ]);
    }

    /**
     * @Route("/mission", name="mission")
     */
    public function mission(): Response
    {
        return $this->render('mission.html.twig', [
            'controller_name' => 'WelcomeController',
        ]);
    }

    /**
     * @Route("/structuration", name="structuration")
     */
    public function structuration(): Response
    {
        return $this->render('structuration.html.twig', [
            'controller_name' => 'WelcomeController',
        ]);
    }

    /**
     * @Route("/postdetail", name="postdetail")
     */
    public function post_detail(): Response
    {
        return $this->render('post-detail.html.twig', []);
    }
}
