<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    /**
     * @Route("/actualites", name="actualites")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $posts = $doctrine->getRepository(Post::class)->findAll();
        $first = count($posts) > 0 ? $posts[0] : '';

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'first' => $first
        ]);
    }

    /**
     * @Route("/actualites/{title}/{id}", name="actu_detail")
     */
    public function detail(ManagerRegistry $doctrine, $id): Response
    {
        $post = $doctrine->getRepository(Post::class)->find($id);

        $posts = $doctrine->getRepository(Post::class)->findAll();

        $next = null;
        $prev = null;
        $i = 0;

        foreach ($posts as $p) {
            if ($p->getId() == $post->getId()) {

                if ($i > 0) {
                    $prev = $posts[$i - 1];
                }

                if ($i < count($posts) - 1) {
                    $next = $posts[$i + 1];
                }
            }
            $i++;
        }

        return $this->render('post/detail.html.twig', [
            'post' => $post,
            'prev' => $prev,
            'next' => $next,
        ]);
    }

    #[Route('/admin/posts/delete/{id}', name: 'delete_post', methods: ['POST'])]
    public function delete(ManagerRegistry $doctrine, $id, EntityManagerInterface $em): Response
    {
        $post = $doctrine->getRepository(Post::class)->find($id);
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('posts_admin');
    }

    #[Route('/admin/posts/{id}', name: 'edit_post', methods: ['GET', 'POST'])]
    public function edit(ManagerRegistry $doctrine, $id, EntityManagerInterface $em, Request $request, SluggerInterface $slugger): Response
    {
        $post = $doctrine->getRepository(Post::class)->find($id);

        $old_img = $post->getImgPath();

        $edit_form = $this->createFormBuilder($post)
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Titre de l\'actualité'], 'label' => false])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez une description'], 'label' => false])
            ->add('rapport_link', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Lien du rapport'], 'label' => false, 'required' => false])
            ->add('img_path', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => "Image à la une", 'data_class' => null, 'required' => false])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        $edit_form->handleRequest($request);

        if ($edit_form->isSubmitted() && $edit_form->isValid()) {
            $datas = $edit_form->getData();
            $image = $edit_form->get('img_path')->getData();

            $post
                ->setTitle($datas->getTitle())
                ->setDescription($datas->getDescription())
                ->setImgPath($old_img)
                ->setRapportLink($datas->getRapportLink());


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
                } else {
                    $this->addFlash('success', 0);
                    $posts = $doctrine->getRepository(Post::class)->findAll();
                    return $this->render('admin/posts/edit.html.twig', [
                        'post' => $post,
                        'edit_form' => $edit_form->createView(),
                        'message' => 'Vous devez importer comme image à la une une image au fomat : .jpeg, .jpg, .png ou .webp'
                    ]);
                }
            }

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 1);

            $new_posts = $doctrine->getRepository(Post::class)->findAll();

            return $this->render('admin/posts/edit.html.twig', [
                'post' => $post,
                'edit_form' => $edit_form->createView(),
                'message' => 'Actualité "' . $post->getTitle()  . '" modifiée avec succès'
            ]);
        }

        return $this->render('admin/posts/edit.html.twig', [
            'post' => $post,
            'edit_form' => $edit_form->createView(),
        ]);
    }

    #[Route('/rececntscl/posts/', name: 'recent_post')]
    public function recentes(ManagerRegistry $doctrine): Response
    {
        $posts = $doctrine->getRepository(Post::class)->findAll();
        return $this->render('post/_recent.html.twig', [
            'recents' => array_slice($posts, 0, 5)
        ]);
    }
}
