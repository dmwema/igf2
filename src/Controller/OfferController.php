<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OfferController extends AbstractController
{
    /**
     * @Route("/emplois", name="emplois")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $offers = $doctrine->getRepository(Offer::class)->findAll();

        return $this->render('offer/index.html.twig', [
            'offers' => $offers,
        ]);
    }

    /**
     * @Route("/emplois/{title}/{id}", name="offer_detail")
     */
    public function detail(ManagerRegistry $doctrine, $id, Request $request, EntityManagerInterface $em): Response
    {
        $offer = $doctrine->getRepository(Offer::class)->find($id);

        $candidature_form = $this->createFormBuilder()
            ->add('prenom', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre prenom'], 'label' => 'Prenom'])
            ->add('nom', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre nom'], 'label' => 'Nom'])
            ->add('postnom', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre postnom'], 'label' => 'Postnom'])
            ->add('email', EmailType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre adresse mail'], 'label' => 'Adresse mail'])
            ->add('naissance', DateType::class, ['attr' => [
                'class' => 'birth_select', 'placeholder' => 'Votre date de naissance'
            ], 'label' => 'Date de naissance'])
            ->add('phone', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre numéro de téléphone'], 'label' => 'Numéro de téléphone'])
            ->add('ville', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre ville de résidence'], 'label' => 'Ville de résidence'])
            ->add('adresse', TextareaType::class, ['attr' => ['class' => 'form-control'], 'label' => 'Adresse de residence actuelle'])
            ->add('bio', TextareaType::class, ['attr' => ['class' => 'form-control'], 'label' => 'Parlez brièvement de vous'])
            ->add('cv', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => 'ENvoyer votre CV'])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Envoyer la demande'])
            ->setMethod('POST')
            ->getForm();

        $candidature_form->handleRequest($request);

        if ($candidature_form->isSubmitted() && $candidature_form->isValid()) {
            $datas = $candidature_form->getData();

            $candidature = new Candidature();
            $candidature
                ->setPrenom($datas['prenom'])
                ->setNom($datas['nom'])
                ->setPostnom($datas['postnom'])
                ->setNaissance($datas['naissance'])
                ->setVille($datas['ville'])
                ->setAdresse($datas['adresse'])
                ->setEmail($datas['email'])
                ->setPhone($datas['phone'])
                ->setBio($datas['bio'])
                ->setCv($datas['cv']);

            $em->persist($candidature);
            $em->flush();

            return 'ok';
        }

        return $this->render('offer/detail.html.twig', [
            'offer' => $offer,
            'candidature_form' => $candidature_form->createView()
        ]);
    }
}
