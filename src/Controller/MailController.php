<?php

namespace App\Controller;

use App\Entity\Rapport;
use Doctrine\Persistence\ManagerRegistry;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    /**
     * @Route("/mail/{email}/{rapport_id}", name="send_mail")
     */
    public function send_mail(Swift_Mailer $mailer, $email, $rapport_id, ManagerRegistry $doctrine): Response
    {
        $rapport = $doctrine->getRepository(Rapport::class)->find($rapport_id);
        $email = (new Swift_Message())
            ->setFrom('danielmwemakapwe@gmail.com')
            ->setTo($email)
            ->setSubject('téléchargement fichier')
            ->setBody(
                $this->renderView(
                    'mail/index.html.twig',
                    ['rapport' => $rapport]
                ),
                'text/html'
            );

        $mailer->send($email);

        return $this->render('rapport/mailsent.html.twig');
    }
}
