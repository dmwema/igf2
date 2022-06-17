<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Breve;
use App\Entity\Candidature;
use App\Entity\Denoncement;
use App\Entity\Download;
use App\Entity\Offer;
use App\Entity\Photo;
use App\Entity\Post;
use App\Entity\Press;
use App\Entity\Rapport;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(Request $request, EntityManagerInterface $em, ManagerRegistry $doctrine)
    {
        $rapports = count($doctrine->getRepository(Rapport::class)->findAll());
        $posts = count($doctrine->getRepository(Post::class)->findAll());
        $downloads = count($doctrine->getRepository(Download::class)->findAll());
        $photos = count($doctrine->getRepository(Photo::class)->findAll());
        $offers = count($doctrine->getRepository(Offer::class)->findAll());
        $candidatures = count($doctrine->getRepository(Candidature::class)->findAll());
        $presses = count($doctrine->getRepository(Press::class)->findAll());
        $users = count($doctrine->getRepository(User::class)->findAll());

        $datas = [
            'Rapports' => [
                'icon' => 'fas fa-file-pdf',
                'count' => $rapports
            ],
            'Actualités' => [
                'icon' => 'fas fa-newspaper',
                'count' => $posts
            ],
            'Téléchargéments' => [
                'icon' => 'fas fa-download',
                'count' => $downloads
            ],
            'Photos' => [
                'icon' => 'fas fa-file-image',
                'count' => $photos
            ],
            'Offres d\'emploie' => [
                'icon' => 'fas fa-search',
                'count' => $offers
            ],
            'Candidatures' => [
                'icon' => 'fas fa-file-alt',
                'count' => $candidatures
            ],
            'Press' => [
                'icon' => 'fas fa-video',
                'count' => $presses
            ],
            'Utilisateurs' => [
                'icon' => 'fas fa-users',
                'count' => $users
            ],
        ];

        return $this->render('admin/index.html.twig', compact('datas'));
    }

    /**
     * @Route("/admin/login", name="login_admin")
     */
    public function login(Request $request, EntityManagerInterface $em)
    {
        $login_form = $this->createFormBuilder()
            ->add('email', EmailType::class, ['attr' => ['class' => 'login-input form-control', 'placeholder' => 'Entrez l\'adresse email'], 'label' => false])
            ->add('password', PasswordType::class, ['attr' => ['class' => 'login-input form-control', 'placeholder' => 'Entrez le mot de passe'], 'label' => false])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'media-link',], 'label' => 'Se connecter'])
            ->setMethod('POST')
            ->getForm();

        $has_error = false;
        $error_message = "";


        $login_form->handleRequest($request);

        if ($login_form->isSubmitted() && $login_form->isValid()) {
            $datas = $login_form->getData();
            $admin = $em->getRepository(Admin::class)->findOneBy(['email' => $datas['email']]);
            $email = $datas['email'];
            $password = $datas['password'];

            //dd($passwordHasher->isPasswordValid($admin, $password));

            if ($admin === null) {
                $has_error = true;
                $error_message = "Adresse email ou mot de passe incorrecte";
            } else {
                dd('mail ok');
                /*if (Hash::check($password, $admin->getPassword())) {
                    dd('ok');
                } else {
                    $has_error = true;
                    $error_message = "Adresse email ou mot de passe incorrecte";
                }*/
            }
        }

        return $this->render('admin/login.html.twig', [
            'has_error' => $has_error, 'error_message' => $error_message,
            'login_form' => $login_form->createView(),
            'post' => $_POST
        ]);
    }

    /**
     * @Route("/admin/rapports", name="rapports_admin")
     */
    public function rapports(ManagerRegistry $doctrine, EntityManagerInterface $em, Request $request, SluggerInterface $slugger)
    {
        $create_form = $this->createFormBuilder()
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Titre du rapport'], 'label' => false])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez une description'], 'label' => false])
            ->add('file_path', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => "Fichier"])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $rapports = $doctrine->getRepository(Rapport::class)->findAll();
        $create_form->handleRequest($request);

        if ($create_form->isSubmitted() && $create_form->isValid()) {
            $datas = $create_form->getData();
            $rapport = new Rapport();
            $file = $create_form->get('file_path')->getData();

            $rapport
                ->setTitle($datas['title'])
                ->setDescription($datas['description']);

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

                    $em->persist($rapport);
                    $em->flush();

                    $this->addFlash('success', 1);

                    $new_rapports = $doctrine->getRepository(Rapport::class)->findAll();

                    return $this->render('admin/rapports/index.html.twig', [
                        'rapports' => $new_rapports,
                        'create_form' => $create_form->createView(),
                        'message' => 'Rapport "' . $rapport->getTitle()  . '" enrégistré dans la base de données avec succès'
                    ]);
                } else {
                    $this->addFlash('success', 0);
                    return $this->render('admin/rapports/index.html.twig', [
                        'rapports' => $rapports,
                        'create_form' => $create_form->createView(),
                        'message' => 'Le rapport doit être au format .pdf'
                    ]);
                }
            }
        }

        return $this->render('admin/rapports/index.html.twig', [
            'rapports' => $rapports,
            'create_form' => $create_form->createView()
        ]);
    }

    /**
     * @Route("/admin/actualites", name="posts_admin")
     */
    public function posts(ManagerRegistry $doctrine, EntityManagerInterface $em, Request $request, SluggerInterface $slugger)
    {
        $create_form = $this->createFormBuilder()
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Titre de l\'actualité'], 'label' => false])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez une description'], 'label' => false])
            ->add('rapport_link', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Lien du rapport'], 'label' => false, 'required' => false])
            ->add('img_path', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => "Image à la une"])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $posts = $doctrine->getRepository(Post::class)->findAll();
        $create_form->handleRequest($request);

        if ($create_form->isSubmitted() && $create_form->isValid()) {
            $datas = $create_form->getData();
            $post = new Post();
            $image = $create_form->get('img_path')->getData();

            $post
                ->setTitle($datas['title'])
                ->setDescription($datas['description'])
                ->setRapportLink($datas['rapport_link']);

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                if ($image->guessExtension() === 'jpeg' || $image->guessExtension() === 'png' || $image->guessExtension() === 'jpg' || $image->guessExtension() === 'webp') {

                    // Move the file to the directory where brochures are stored
                    try {
                        $image->move(
                            $this->getParameter('posts'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        dd($e);
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $post
                        ->setImgPath($newFilename);

                    $em->persist($post);
                    $em->flush();

                    $this->addFlash('success', 1);

                    $new_posts = $doctrine->getRepository(Post::class)->findAll();

                    return $this->render('admin/posts/index.html.twig', [
                        'posts' => $new_posts,
                        'create_form' => $create_form->createView(),
                        'message' => 'Actualité "' . $post->getTitle()  . '" enrégistrée dans la base de données avec succès'
                    ]);
                } else {
                    $this->addFlash('success', 0);
                    return $this->render('admin/posts/index.html.twig', [
                        'posts' => $posts,
                        'create_form' => $create_form->createView(),
                        'message' => 'Vous devez importer comme image à la une une image au fomat : .jpeg, .jpg, .png ou .webp'
                    ]);
                }
            }
        }

        return $this->render('admin/posts/index.html.twig', [
            'posts' => $posts,
            'create_form' => $create_form->createView()
        ]);
    }

    /**
     * @Route("/admin/telechargement", name="downloads_admin")
     */
    public function downloads(ManagerRegistry $doctrine, EntityManagerInterface $em)
    {
        $downloads = $doctrine->getRepository(Download::class)->findAll();

        return $this->render('admin/downloads/index.html.twig', ['downloads' => $downloads]);
    }

    /**
     * @Route("/admin/photos", name="photos_admin")
     */
    public function photos(ManagerRegistry $doctrine, EntityManagerInterface $em, Request $request, SluggerInterface $slugger)
    {

        $create_form = $this->createFormBuilder()
            ->add('path', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => "Image"])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez une description'], 'label' => false])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $photos = $doctrine->getRepository(Photo::class)->findAll();
        $create_form->handleRequest($request);

        if ($create_form->isSubmitted() && $create_form->isValid()) {
            $datas = $create_form->getData();
            $photo = new Photo();
            $image = $create_form->get('path')->getData();

            $photo
                ->setDescription($datas['description']);

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the image name as part of the URL
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

                    $em->persist($photo);
                    $em->flush();

                    $this->addFlash('success', 1);

                    $new_photos = $doctrine->getRepository(Photo::class)->findAll();

                    return $this->render('admin/photos/index.html.twig', [
                        'photos' => $new_photos,
                        'create_form' => $create_form->createView(),
                        'message' => 'Image enrégistrée dans la base de données avec succès'
                    ]);
                } else {
                    $this->addFlash('success', 0);
                    return $this->render('admin/photos/index.html.twig', [
                        'photos' => $photos,
                        'create_form' => $create_form->createView(),
                        'message' => 'L\'image doit être au format : .jpeg, .jpg, .png ou .webp'
                    ]);
                }
            }
        }

        return $this->render('admin/photos/index.html.twig', [
            'photos' => $photos,
            'create_form' => $create_form->createView(),
        ]);
    }

    /**
     * @Route("/admin/offers", name="offers_admin")
     */
    public function offers(ManagerRegistry $doctrine, EntityManagerInterface $em, Request $request, SluggerInterface $slugger)
    {
        $create_form = $this->createFormBuilder()
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Poste de l\'offre'], 'label' => false])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Description'], 'label' => false])
            ->add('image', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => "Image (optionnel)"])
            ->add('file', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => "Fichier descriptif (description + critères)"])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $offers = $doctrine->getRepository(Offer::class)->findAll();
        $create_form->handleRequest($request);

        if ($create_form->isSubmitted() && $create_form->isValid()) {
            $datas = $create_form->getData();
            $offer = new Offer();
            $image = $create_form->get('image')->getData();
            $file = $create_form->get('file')->getData();

            $offer
                ->setTitle($datas['title'])
                ->setDescription($datas['description']);

            if ($image && $file) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                $originalFilenameDesc = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilenameDesc = $slugger->slug($originalFilenameDesc);
                $newFilenameDesc = $safeFilenameDesc . '-' . uniqid() . '.' . $file->guessExtension();

                if ($image->guessExtension() === 'jpeg' || $image->guessExtension() === 'png' || $image->guessExtension() === 'jpg' || $image->guessExtension() === 'webp') {
                    if ($file->guessExtension() === 'pdf') {
                        // Move the file to the directory where brochures are stored
                        try {
                            $image->move(
                                $this->getParameter('offers'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            dd($e);
                        }

                        // Move the file to the directory where brochures are stored
                        try {
                            $file->move(
                                $this->getParameter('offersDesc'),
                                $newFilenameDesc
                            );
                        } catch (FileException $e) {
                            dd($e);
                        }

                        $offer
                            ->setImage($newFilename)
                            ->setFile($newFilenameDesc);

                        $em->persist($offer);
                        $em->flush();

                        $this->addFlash('success', 1);

                        $new_offers = $doctrine->getRepository(Offer::class)->findAll();

                        return $this->render('admin/offers/index.html.twig', [
                            'offers' => $new_offers,
                            'create_form' => $create_form->createView(),
                            'message' => 'Offre au post de "' . $offer->getTitle()  . '" enrégistrée dans la base de données avec succès'
                        ]);
                    } else {
                        $this->addFlash('success', 0);
                        return $this->render('admin/offers/index.html.twig', [
                            'offers' => $offers,
                            'create_form' => $create_form->createView(),
                            'message' => 'Le fichier de description doit être au format .pdf'
                        ]);
                    }
                } else {
                    $this->addFlash('success', 0);
                    return $this->render('admin/offers/index.html.twig', [
                        'offers' => $offers,
                        'create_form' => $create_form->createView(),
                        'message' => 'Vous devez importer comme image à la une une image au fomat : .jpeg, .jpg, .png ou .webp'
                    ]);
                }
            }
        }

        return $this->render('admin/offers/index.html.twig', [
            'offers' => $offers,
            'create_form' => $create_form->createView()
        ]);
    }

    /**
     * @Route("/admin/candidatures", name="candidatures_admin")
     */
    public function candidatures(ManagerRegistry $doctrine, EntityManagerInterface $em)
    {
        $candidatures = $doctrine->getRepository(Candidature::class)->findAll();

        return $this->render('admin/candidatures/index.html.twig', ['candidatures' => $candidatures]);
    }

    /**
     * @Route("/admin/presses", name="presses_admin")
     */
    public function presses(ManagerRegistry $doctrine, EntityManagerInterface $em, Request $request)
    {
        $create_form = $this->createFormBuilder()
            ->add('url', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Lien'], 'label' => false])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez la description'], 'label' => false])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $presses = $doctrine->getRepository(Press::class)->findAll();
        $create_form->handleRequest($request);

        if ($create_form->isSubmitted() && $create_form->isValid()) {
            $datas = $create_form->getData();
            $press = new Press();

            $press
                ->setUrl($datas['url'])
                ->setDescription($datas['description']);

            $em->persist($press);
            $em->flush();

            $this->addFlash('success', 1);

            $new_presses = $doctrine->getRepository(Press::class)->findAll();

            return $this->render('admin/presses/index.html.twig', [
                'presses' => $new_presses,
                'create_form' => $create_form->createView(),
                'message' => 'Vidéo de presse enrégistrée dans la base de données avec succès'
            ]);
        }

        return $this->render('admin/presses/index.html.twig', [
            'presses' => $presses,
            'create_form' => $create_form->createView()
        ]);
    }

    /**
     * @Route("/admin/users", name="users_admin")
     */
    public function users(ManagerRegistry $doctrine, EntityManagerInterface $em)
    {
        $users = $doctrine->getRepository(User::class)->findAll();

        return $this->render('admin/users/index.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/admin/denoncements", name="denoncements_admin")
     */
    public function denoncements(ManagerRegistry $doctrine, EntityManagerInterface $em)
    {
        $denoncements = $doctrine->getRepository(Denoncement::class)->findAll();

        return $this->render('admin/denoncements/index.html.twig', ['denoncements' => $denoncements]);
    }

    #[Route('/admin/denoncements/delete/{id}', name: 'delete_denoncement', methods: ['POST'])]
    public function delete(ManagerRegistry $doctrine, $id, EntityManagerInterface $em): Response
    {
        $denoncement = $doctrine->getRepository(Denoncement::class)->find($id);
        $em->remove($denoncement);
        $em->flush();

        return $this->redirectToRoute('denoncements_admin');
    }

    /**
     * @Route("/admin/breves", name="breves_admin")
     */
    public function breves(ManagerRegistry $doctrine, EntityManagerInterface $em, Request $request)
    {
        $create_form = $this->createFormBuilder()
            ->add('content', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Intitulé'], 'label' => false])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $breves = $doctrine->getRepository(Breve::class)->findAll();
        $create_form->handleRequest($request);

        if ($create_form->isSubmitted() && $create_form->isValid()) {
            $datas = $create_form->getData();
            $breve = new Breve();

            $breve
                ->setContent($datas['content']);

            $em->persist($breve);
            $em->flush();

            $this->addFlash('success', 1);

            $new_breves = $doctrine->getRepository(Breve::class)->findAll();

            return $this->render('admin/breves/index.html.twig', [
                'breves' => $new_breves,
                'create_form' => $create_form->createView(),
                'message' => 'Brève enrégistrée dans la base de données avec succès'
            ]);
        }

        return $this->render('admin/breves/index.html.twig', [
            'breves' => $breves,
            'create_form' => $create_form->createView(),
        ]);
    }

    #[Route('/admin/breves/delete/{id}', name: 'delete_breve', methods: ['POST'])]
    public function delete_breves(ManagerRegistry $doctrine, $id, EntityManagerInterface $em): Response
    {
        $breve = $doctrine->getRepository(Breve::class)->find($id);
        $em->remove($breve);
        $em->flush();

        return $this->redirectToRoute('breves_admin');
    }

    #[Route('/brevescalculus/breves/', name: 'breves')]
    public function breves_all(ManagerRegistry $doctrine): Response
    {
        $breves = $doctrine->getRepository(Breve::class)->findAll();
        return $this->render('breve/_breves.html.twig', [
            'breves' => $breves
        ]);
    }
}
