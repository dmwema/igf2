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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Date;

class DenoncementController extends AbstractController
{
    /**
     * @Route("/denoncer", name="denoncer")
     */
    public function index(SluggerInterface $slugger, ManagerRegistry $doctrine, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
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

        $message = '';

        $denoncement_form->handleRequest($request);

        if ($denoncement_form->isSubmitted() && $denoncement_form->isValid()) {
            $datas = $denoncement_form->getData();
            $file = $denoncement_form->get('fichier')->getData();

            //dd($file);

            $denoncement = new Denoncement();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                $denoncement
                    ->setFichier($newFilename);

                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('denoncements'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    dd($e);
                }
            }

            $denoncement
                ->setCivilite($datas['civilite'])
                ->setNom($datas['nom'])
                ->setPrenom($datas['prenom'])
                ->setEmail($datas['email'])
                ->setProfession($datas['profession'])
                ->setPhone($datas['phone'])
                ->setMotif($datas['motif'])
                ->setSeen(0)
                ->setCreatedAt(new \DateTimeImmutable());


            $em->persist($denoncement);
            $em->flush();

            $this->addFlash('success', 1);

            $message = 'Votre denoncement a été enrégistré avec succès';
        }

        return $this->render('denoncement/index.html.twig', [
            'denoncement_form' => $denoncement_form->createView(),
            'message' => $message
        ]);
    }
}
