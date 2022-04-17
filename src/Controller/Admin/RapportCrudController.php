<?php

namespace App\Controller\Admin;

use App\Entity\Rapport;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RapportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Rapport::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
