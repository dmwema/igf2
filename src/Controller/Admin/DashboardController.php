<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Entity\Photo;
use App\Entity\Post;
use App\Entity\Rapport;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/azazee", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(OfferCrudController::class)->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Igf');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Retour au site', 'fas fa-home', 'home');
        yield MenuItem::linkToRoute('Rapports', 'fas fa-file-lines', Rapport::class);
        yield MenuItem::linkToRoute('Actualités', 'fas fa-square-rss', Post::class);
        yield MenuItem::linkToRoute('Galerie', 'fas fa-file-image', Photo::class);
        yield MenuItem::linkToRoute('Emploies', 'fas fa-map', Offer::class);
    }
}
