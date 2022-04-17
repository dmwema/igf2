<?php

namespace App\Controller;

use App\Entity\Download;
use App\Entity\Rapport;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RapportController extends AbstractController
{
    /**
     * @Route("/rapports", name="rapports")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $rapports = $doctrine->getRepository(Rapport::class)->findAll();

        return $this->render('rapport/index.html.twig', [
            'controller_name' => 'RapportController',
            'rapports' => $rapports,
        ]);
    }

    /**
     * @Route("/rapports/{title}/{id}", name="rapport")
     */
    public function download(int $id, ManagerRegistry $doctrine, Request $request, EntityManagerInterface $em)
    {
        $rapport = $doctrine->getRepository(Rapport::class)->find($id);

        $download_form = $this->createFormBuilder()
            ->add('firstname', TextType::class, ['attr' => ['class' => 'form-input', 'placeholder' => 'Votre prenom'], 'label' => false])
            ->add('lastname', TextType::class, ['attr' => ['class' => 'form-input', 'placeholder' => 'Votre nom'], 'label' => false])
            ->add('email', EmailType::class, ['attr' => ['class' => 'form-input', 'placeholder' => 'Votre adresse mail'], 'label' => false])
            ->add('newsletter', CheckboxType::class, ['attr' => ['class' => 'form-check'], 'label' => "Recevoir des emails"])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'form-submit',], 'label' => 'Télécharger'])
            ->setMethod('POST')
            ->getForm();

        $download_form->handleRequest($request);

        if ($download_form->isSubmitted() && $download_form->isValid()) {
            $datas = $download_form->getData();
            $user = $em->getRepository(User::class)->findBy(['email' => $datas['email']]);

            $current_user = null;
            if (count($user) == 0) {
                $current_user = new User();
                $current_user
                    ->setFirstname($datas['lastname'])
                    ->setLastname($datas['firstname'])
                    ->setEmail($datas['email']);

                $em->persist($current_user);
            } else {
                $current_user = $em->getRepository(User::class)->findOneBy(['email' => $datas['email']]);
            }

            $download = new Download();
            $download
                ->setUser($current_user)
                ->setRapport($rapport)
                ->setDate(new DateTime());

            $em->persist($download);

            // validate datas

            //send file mail
            return $this->redirectToRoute('send_mail', [
                'email' => $current_user->getEmail(),
                'rapport_id' => $rapport->getId(),
            ]);

            $em->flush();
        }

        return $this->render('rapport/download.html.twig', [
            'rapport' => $rapport,
            'download_form' => $download_form->createView()
        ]);
    }
}
