<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
