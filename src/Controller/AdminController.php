<?php

namespace App\Controller;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
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

        return $this->render('admin/index.html.twig', [
            'has_error' => $has_error, 'error_message' => $error_message,
            'login_form' => $login_form->createView(),
            'post' => $_POST
        ]);
    }
}
