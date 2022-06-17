<?php

namespace App\Controller;

use App\Entity\Download;
use App\Entity\Offer;
use App\Entity\Post;
use App\Entity\Rapport;
use App\Entity\User;
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
        $c_users = count($doctrine->getRepository(User::class)->findAll());
        $c_rapports = count($doctrine->getRepository(Rapport::class)->findAll());
        $c_downloads = count($doctrine->getRepository(Download::class)->findAll());
        $c_offers = count($doctrine->getRepository(Offer::class)->findAll());
        $posts = $doctrine->getRepository(Post::class)->findAll();
        return $this->render('welcome/index.html.twig', [
            'controller_name' => 'WelcomeController',
            'posts' => array_slice($posts, 0, 5),
            'c_users' => $c_users,
            'c_rapports' => $c_rapports,
            'c_downloads' => $c_downloads,
            'c_offers' => $c_offers,
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
