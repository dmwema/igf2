<?php

namespace App\Controller;

use App\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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

    /**
     * @Route("/admin/photos/delete/{id}", name="delete_photo", methods={"POST"})
     */
    public function delete(ManagerRegistry $doctrine, $id, EntityManagerInterface $em): Response
    {
        $photo = $doctrine->getRepository(Photo::class)->find($id);
        $em->remove($photo);
        $em->flush();

        return $this->redirectToRoute('photos_admin');
    }

    /**
     * @Route("/admin/photos/{id}", name="edit_photo", methods={"POST", "GET"})
     */
    public function edit(ManagerRegistry $doctrine, $id, EntityManagerInterface $em, Request $request, SluggerInterface $slugger): Response
    {
        $photo = $doctrine->getRepository(Photo::class)->find($id);

        $old_img = $photo->getPath();

        $edit_form = $this->createFormBuilder($photo)
            ->add('path', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => "Image", 'data_class' => null, 'required' => true])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez une description'], 'label' => false])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $edit_form->handleRequest($request);

        if ($edit_form->isSubmitted() && $edit_form->isValid()) {
            $datas = $edit_form->getData();
            $image = $edit_form->get('path')->getData();

            $photo
                ->setDescription($datas->getDescription())
                ->setPath($old_img);


            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                if ($image->guessExtension() === 'jpeg' || $image->guessExtension() === 'png' || $image->guessExtension() === 'jpg' || $image->guessExtension() === 'webp') {

                    // Move the file to the directory where brochures are stored
                    try {
                        $image->move(
                            $this->getParameter('photos'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        dd($e);
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $photo
                        ->setPath($newFilename);
                } else {
                    $this->addFlash('success', 0);
                    $photos = $doctrine->getRepository(Photo::class)->findAll();
                    return $this->render('admin/photos/edit.html.twig', [
                        'photo' => $photo,
                        'edit_form' => $edit_form->createView(),
                        'message' => 'La photo doit être au format : .jpeg, .jpg, .png ou .webp'
                    ]);
                }
            }

            $em->persist($photo);
            $em->flush();

            $this->addFlash('success', 1);

            $new_photos = $doctrine->getRepository(Photo::class)->findAll();

            return $this->render('admin/photos/edit.html.twig', [
                'photo' => $photo,
                'edit_form' => $edit_form->createView(),
                'message' => 'Informations de l\'image modifiées avec succès'
            ]);
        }

        return $this->render('admin/photos/edit.html.twig', [
            'photo' => $photo,
            'edit_form' => $edit_form->createView(),
        ]);
    }
}
