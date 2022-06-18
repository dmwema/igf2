<?php

namespace App\Controller;

use App\Entity\Download;
use App\Entity\Rapport;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function download(Swift_Mailer $mailer, int $id, ManagerRegistry $doctrine, Request $request, EntityManagerInterface $em)
    {
        $rapport = $doctrine->getRepository(Rapport::class)->find($id);

        $download_form = $this->createFormBuilder()
            ->add('firstname', TextType::class, ['attr' => ['class' => 'form-input', 'placeholder' => 'Votre prenom'], 'label' => false])
            ->add('lastname', TextType::class, ['attr' => ['class' => 'form-input', 'placeholder' => 'Votre nom'], 'label' => false])
            ->add('email', EmailType::class, ['attr' => ['class' => 'form-input', 'placeholder' => 'Votre adresse mail'], 'label' => false])
            ->add('newsletter', CheckboxType::class, ['attr' => ['class' => 'form-check'], 'label' => "Recevoir des emails", 'required' => false])
            ->add('civilite', ChoiceType::class, ['attr' => ['class' => 'form-select'], 'label' => 'Civilité', 'choices' => [
                'Mr' => 'Mr',
                'Mme' => 'Mme',
                'Mlle' => 'Mlle',
            ]])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'form-submit',], 'label' => 'Télécharger'])
            ->setMethod('POST')
            ->getForm();

        $download_form->handleRequest($request);

        if ($download_form->isSubmitted() && $download_form->isValid()) {
            $datas = $download_form->getData();
            $user = $em->getRepository(User::class)->findBy(['email' => $datas['email']]);

            //dd(count($user));

            $current_user = null;
            if (count($user) == 0) {
                $current_user = new User();
                $current_user
                    ->setFirstname($datas['lastname'])
                    ->setCivilite($datas['civilite'])
                    ->setLastname($datas['firstname'])
                    ->setEmail($datas['email']);

                $em->persist($current_user);
            } else {
                $current_user = $em->getRepository(User::class)->findOneBy(['email' => $datas['email']]);
            }

            $download = $em->getRepository(Download::class)->findOneBy(['user' => $current_user->getId(), 'rapport' => $rapport->getId()]);

            if ($download === null) {
                $download = new Download();
                $download
                    ->setUser($current_user)
                    ->setRapport($rapport)
                    ->setDate(new DateTime());
                $em->persist($download);
                $em->flush();
            }

            // validate datas

            //send file mail
            $emails = (new Swift_Message($rapport->getTitle()))
                ->setFrom('noreply@igf.gouv.cd', 'IGF RDC')
                ->setTo($current_user->getEmail())
                ->setBody(
                    $this->renderView(
                        // templates/emails/registration.html.twig
                        'mail/index.html.twig',
                        ['rapport' => $rapport]
                    ),
                    'text/html'
                );

            $mailer->send($emails);

            return $this->render('rapport/mailsent.html.twig');
        }

        return $this->render('rapport/download.html.twig', [
            'rapport' => $rapport,
            'download_form' => $download_form->createView()
        ]);
    }

    #[Route('/admin/rapports/delete/{id}', name: 'delete_rapport', methods: ['POST'])]
    public function delete(ManagerRegistry $doctrine, $id, EntityManagerInterface $em): Response
    {
        $rapport = $doctrine->getRepository(Rapport::class)->find($id);
        $em->remove($rapport);
        $em->flush();

        return $this->redirectToRoute('rapports_admin');
    }

    #[Route('/admin/rapports/{id}', name: 'edit_rapport', methods: ['GET', 'POST'])]
    public function edit(ManagerRegistry $doctrine, $id, EntityManagerInterface $em, Request $request, SluggerInterface $slugger): Response
    {
        $rapport = $doctrine->getRepository(Rapport::class)->find($id);

        $old_file = $rapport->getFilePath();

        $edit_form = $this->createFormBuilder($rapport)
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Titre du rapport'], 'label' => false])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez une description'], 'label' => false])
            ->add('file_path', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => false, 'data_class' => null, 'required' => true])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $edit_form->handleRequest($request);

        if ($edit_form->isSubmitted() && $edit_form->isValid()) {
            $datas = $edit_form->getData();
            $file = $edit_form->get('file_path')->getData();

            $rapport
                ->setTitle($datas->getTitle())
                ->setDescription($datas->getDescription())
                ->setFilePath($old_file);


            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                if ($file->guessExtension() === 'pdf') {

                    // Move the file to the directory where brochures are stored
                    try {
                        $file->move(
                            $this->getParameter('rapports'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        dd($e);
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $rapport
                        ->setFilePath($newFilename);
                } else {
                    $this->addFlash('success', 0);
                    return $this->render('admin/rapports/edit.html.twig', [
                        'rapport' => $rapport,
                        'edit_form' => $edit_form->createView(),
                        'message' => 'Le rapport doit être au format .pdf'
                    ]);
                }
            }

            $em->persist($rapport);
            $em->flush();

            $this->addFlash('success', 1);

            return $this->render('admin/rapports/edit.html.twig', [
                'rapport' => $rapport,
                'edit_form' => $edit_form->createView(),
                'message' => 'Rapport "' . $rapport->getTitle()  . '" modifiée avec succès'
            ]);
        }

        return $this->render('admin/rapports/edit.html.twig', [
            'rapport' => $rapport,
            'edit_form' => $edit_form->createView(),
        ]);
    }


    #[Route('/admin/downloads/delete/{id}', name: 'delete_download', methods: ['POST'])]
    public function delete_download(ManagerRegistry $doctrine, $id, EntityManagerInterface $em): Response
    {
        $download = $doctrine->getRepository(Download::class)->find($id);
        $em->remove($download);
        $em->flush();

        return $this->redirectToRoute('downloads_admin');
    }
}
