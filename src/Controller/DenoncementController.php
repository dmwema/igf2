<?php

namespace App\Controller;

use App\Entity\Denoncement;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

class DenoncementController extends AbstractController
{
    /**
     * @Route("/denoncer", name="denoncer")
     */
    public function index(ManagerRegistry $doctrine, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $denoncement_form = $this->createFormBuilder()
            ->add('civilite', ChoiceType::class, ['attr' => ['class' => 'form-select'], 'label' => 'Civilité', 'choices' => [
                'Mr' => 'Mr',
                'Mme' => 'Mme',
                'Mlle' => 'Mlle',
            ]])
            ->add('nom', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre nom'], 'label' => 'Nom'])
            ->add('prenom', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre prenom'], 'label' => 'Prenom'])
            ->add('email', EmailType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre adresse mail'], 'label' => 'Adresse mail'])
            ->add('profession', TextType::class, ['attr' => [
                'class' => 'form-control',
                'placeholder' => 'Votre profession'
            ], 'label' => 'Profession'])
            ->add('phone', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Votre numéro de téléphone'], 'label' => 'Numéro de téléphone'])
            ->add('motif', TextareaType::class, ['attr' => ['class' => 'form-control'], 'label' => 'Motif de la plainte/denoncement'])
            ->add('fichier', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => 'Pièce ou référence accompagnant la plainte/dénoncement (Facultatif)', 'required' => false])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Envoyer'])
            ->setMethod('POST')
            ->getForm();

        $denoncement_form->handleRequest($request);

        if ($denoncement_form->isSubmitted() && $denoncement_form->isValid()) {
            $datas = $denoncement_form->getData();

            $denoncement = new Denoncement();
            $denoncement
                ->setCivilite($datas['civilite'])
                ->setNom($datas['nom'])
                ->setPrenom($datas['prenom'])
                ->setEmail($datas['email'])
                ->setProfession($datas['profession'])
                ->setPhone($datas['phone'])
                ->setMotif($datas['motif'])
                ->setFichier($datas['fichier'])
                ->setCreatedAt(new \DateTimeImmutable());

            $em->persist($denoncement);
            $em->flush();

            $session->set('success', 'Votre denoncement a été enrégistré avec succès');

            return $this->render('denoncement/index.html.twig', [
                'denoncement_form' => $denoncement_form->createView(),
            ]);
        }

        return $this->render('denoncement/index.html.twig', [
            'denoncement_form' => $denoncement_form->createView(),
        ]);
    }
}
