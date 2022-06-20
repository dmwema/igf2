<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="app_user")
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/admin/users/delete/{id}", name="delete_user", methods={"POST"})
     */
    public function delete(ManagerRegistry $doctrine, $id, EntityManagerInterface $em): Response
    {
        $user = $doctrine->getRepository(User::class)->find($id);
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('users_admin');
    }
}
