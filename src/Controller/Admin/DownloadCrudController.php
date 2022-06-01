<?php

namespace App\Controller\Admin;

use App\Entity\Download;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DownloadCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Download::class;
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
