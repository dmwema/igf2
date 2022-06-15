<?php

namespace App\Controller;

use App\Entity\Rapport;
use Doctrine\Persistence\ManagerRegistry;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    /**
     * @Route("/mail/{email}/{rapport_id}", name="send_mail")
     */
    public function send_mail(Swift_Mailer $mailer, $email, $rapport_id, ManagerRegistry $doctrine): Response
    {
        $rapport = $doctrine->getRepository(Rapport::class)->find($rapport_id);

        $emails = (new Swift_Message($rapport->getTitle()))
            ->setFrom('noreply@igf.gouv.cd', 'IGF RDC')
            ->setTo($email)
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
}
