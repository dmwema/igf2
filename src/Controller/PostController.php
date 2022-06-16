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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $title = $post->getTitle();
        $em->remove($post);
        $em->flush();

        $posts = $doctrine->getRepository(Post::class)->findAll();

        return $this->redirectToRoute('posts_admin');
    }

    #[Route('/admin/posts/{id}', name: 'edit_post', methods: ['GET'])]
    public function edit(ManagerRegistry $doctrine, $id, EntityManagerInterface $em): Response
    {
        $post = $doctrine->getRepository(Post::class)->find($id);

        $edit_form = $this->createFormBuilder($post)
            ->add('title', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Titre de l\'actualité'], 'label' => false])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'Entrez une description'], 'label' => false])
            ->add('img_path', FileType::class, ['attr' => ['class' => 'form-control'], 'label' => "Image à la une", 'data_class' => null])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-primary',], 'label' => 'Enrégistrer'])
            ->setMethod('POST')
            ->getForm();

        return $this->render('admin/posts/edit.html.twig', [
            'post' => $post,
            'edit_form' => $edit_form->createView(),
        ]);
    }
}
