<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'name', 'lft', 'lvl', 'rgt']);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name', 'category.name.label');
        $parent = AssociationField::new('parent', 'category.parent.label');
        $budget = AssociationField::new('budget', 'category.budget.label');
        $id = IntegerField::new('id', 'ID');
        $lft = IntegerField::new('lft');
        $lvl = IntegerField::new('lvl');
        $rgt = IntegerField::new('rgt');
        $root = AssociationField::new('root');
        $children = AssociationField::new('children');
        $detailsToCategories = AssociationField::new('detailsToCategories');
        $expenses = AssociationField::new('expenses');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$name, $parent, $budget];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $lft, $lvl, $rgt, $root, $parent, $children, $detailsToCategories, $expenses, $budget];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $parent, $budget];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $parent, $budget];
        }
    }
}
