<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Offer;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function detail(ManagerRegistry $doctrine, $id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $offer = $doctrine->getRepository(Offer::class)->find($id);

        $candidature_form = $this->createFormBuilder()
            ->add('prenom', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre prenom'], 'label' => 'Prenom'])
            ->add('nom', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre nom'], 'label' => 'Nom'])
            ->add('postnom', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre postnom'], 'label' => 'Postnom'])
            ->add('email', EmailType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre adresse mail'], 'label' => 'Adresse mail'])
            ->add('naissance', DateType::class, ['attr' => [
                'class' => 'birth_select', 'placeholder' => 'Votre date de naissance'
            ], 'label' => 'Date de naissance', 'input' => 'datetime_immutable',])
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
            //dd($datas['naissance']);
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
                ->setCv($datas['cv'])
                ->setCreatedAt(new DateTimeImmutable("now"));

            $em->persist($candidature);
            $em->flush();

            $session->set('success', 'Votre demande a été enrégistrée avec succès');

            return $this->render('offer/detail.html.twig', [
                'offer' => $offer,
                'candidature_form' => $candidature_form->createView()
            ]);
        }

        return $this->render('offer/detail.html.twig', [
            'offer' => $offer,
            'candidature_form' => $candidature_form->createView()
        ]);
    }

    #[Route('/admin/offers/delete/{id}', name: 'delete_offer', methods: ['POST'])]
    public function delete(ManagerRegistry $doctrine, $id, EntityManagerInterface $em): Response
    {
        $offer = $doctrine->getRepository(Offer::class)->find($id);
        $em->remove($offer);
        $em->flush();

        return $this->redirectToRoute('offers_admin');
    }

    #[Route('/admin/offers/{id}', name: 'edit_offer', methods: ['GET', 'POST'])]
    public function edit(ManagerRegistry $doctrine, $id, EntityManagerInterface $em, Request $request, SluggerInterface $slugger): Response
    {
        $offer = $doctrine->getRepository(Offer::class)->find($id);

        $old_img = $offer->getImage();

        $edit_form = $this->createFormBuilder($offer)
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Titre de l\'offre'], 'label' => false])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez une description'], 'label' => false])
            ->add('image', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => "Image", 'data_class' => null, 'required' => true])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $edit_form->handleRequest($request);

        if ($edit_form->isSubmitted() && $edit_form->isValid()) {
            $datas = $edit_form->getData();
            $image = $edit_form->get('image')->getData();

            $offer
                ->setTitle($datas->getTitle())
                ->setDescription($datas->getDescription())
                ->setImage($old_img);


            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                if ($image->guessExtension() === 'jpeg' || $image->guessExtension() === 'png' || $image->guessExtension() === 'jpg' || $image->guessExtension() === 'webp') {

                    // Move the file to the directory where brochures are stored
                    try {
                        $image->move(
                            $this->getParameter('offers'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        dd($e);
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $offer
                        ->setImage($newFilename);
                } else {
                    $this->addFlash('success', 0);
                    $offers = $doctrine->getRepository(Offer::class)->findAll();
                    return $this->render('admin/offers/edit.html.twig', [
                        'offer' => $offer,
                        'edit_form' => $edit_form->createView(),
                        'message' => 'Vous devez importer l\'image au fomat : .jpeg, .jpg, .png ou .webp'
                    ]);
                }
            }

            $em->persist($offer);
            $em->flush();

            $this->addFlash('success', 1);

            return $this->render('admin/offers/edit.html.twig', [
                'offer' => $offer,
                'edit_form' => $edit_form->createView(),
                'message' => 'Offre "' . $offer->getTitle()  . '" modifiée avec succès'
            ]);
        }

        return $this->render('admin/offers/edit.html.twig', [
            'offer' => $offer,
            'edit_form' => $edit_form->createView(),
        ]);
    }
}
