<?php

namespace App\Controller\Admin;

use App\Entity\Denoncement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DenoncementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Denoncement::class;
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
