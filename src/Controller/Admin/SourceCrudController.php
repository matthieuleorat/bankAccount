<?php

/*
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Controller\Admin;

use App\Entity\Source;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SourceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Source::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'number', 'name']);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name', 'source.name.label');
        $number = TextField::new('number', 'source.number.label');
        $statements = AssociationField::new('statements', 'source.statements.label');
        $defaultBudget = AssociationField::new('defaultBudget', 'source.defaultBudget.label');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$name, $number, $statements, $defaultBudget];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $number, $name, $statements, $defaultBudget];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $number, $statements, $defaultBudget];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $number, $statements, $defaultBudget];
        }

        return [];
    }
}
