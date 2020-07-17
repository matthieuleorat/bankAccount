<?php

namespace App\Controller\Admin;

use App\Entity\DetailsToCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DetailsToCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DetailsToCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('DetailsToCategory')
            ->setEntityLabelInPlural('DetailsToCategory')
            ->setSearchFields(['id', 'regex', 'label', 'debit', 'credit', 'date', 'comment']);
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('Filtre');
        $regex = TextField::new('regex');
        $budget = AssociationField::new('budget');
        $criteria = AssociationField::new('criteria');
        $panel2 = FormField::addPanel('Dépense');
        $label = TextField::new('label');
        $category = AssociationField::new('category');
        $debit = TextField::new('debit');
        $credit = TextField::new('credit');
        $date = TextField::new('date');
        $comment = TextareaField::new('comment');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $regex, $label, $debit, $credit, $date, $category];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $regex, $label, $debit, $credit, $date, $comment, $category, $criteria, $budget];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $regex, $budget, $criteria, $panel2, $label, $category, $debit, $credit, $date, $comment];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $regex, $budget, $criteria, $panel2, $label, $category, $debit, $credit, $date, $comment];
        }
    }
}
