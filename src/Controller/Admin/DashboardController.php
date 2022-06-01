<?php

namespace App\Controller\Admin;

use App\Entity\Candidature;
use App\Entity\Denoncement;
use App\Entity\Download;
use App\Entity\Offer;
use App\Entity\Photo;
use App\Entity\Post;
use App\Entity\Rapport;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {

        return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(PostCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Igf2');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Actualités', 'fa fa-tags', Post::class);
        yield MenuItem::linkToCrud('Rapports', 'fa fa-list', Rapport::class);
        yield MenuItem::linkToCrud('Photos', 'fa fa-tags', Photo::class);
        yield MenuItem::linkToCrud('Candidatures', 'fa fa-tags', Candidature::class);
        yield MenuItem::linkToCrud('Dénoncement', 'fa fa-tags', Denoncement::class);
        yield MenuItem::linkToCrud('Téléchargéments', 'fa fa-tags', Download::class);
        yield MenuItem::linkToCrud('Offres d\'emploies', 'fa fa-tags', Offer::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
    }
}
