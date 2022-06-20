<?php

namespace App\Controller;

use App\Entity\Download;
use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\Post;
use App\Entity\Rapport;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
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
    public function contact(Request $request, EntityManagerInterface $em, ManagerRegistry $doctrine): Response
    {
        $create_form = $this->createFormBuilder()
            ->add('names', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez votre nom complet'], 'label' => false])
            ->add('email', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez votre adresse email'], 'label' => false])
            ->add('message', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le message'], 'label' => false])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $create_form->handleRequest($request);
        $message = "";


        if ($create_form->isSubmitted() && $create_form->isValid()) {
            $datas = $create_form->getData();
            $message = new Message();

            $message
                ->setNames($datas['names'])
                ->setEmail($datas['email'])
                ->setSeen(0)
                ->setMessage($datas['message']);

            $em->persist($message);
            $em->flush();

            $message = "Message envoyé avec succès";

            $this->addFlash('success', 1);
        }


        return $this->render('contact.html.twig', [
            'controller_name' => 'WelcomeController',
            'create_form' => $create_form->createView(),
            'message' => $message
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
