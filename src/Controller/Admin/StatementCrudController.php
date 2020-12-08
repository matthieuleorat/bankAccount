<?php

namespace App\Controller\Admin;

use App\Entity\Statement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StatementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Statement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Statement')
            ->setEntityLabelInPlural('Statement')
            ->setSearchFields(['id', 'name', 'startingBalance', 'endingBalance', 'TotalDebit', 'totalCredit', 'remoteFile']);
    }

    /**
     * @param string $pageName
     *
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $startingDate = DateField::new('startingDate');
        $endingDate = DateField::new('endingDate');
        $startingBalance = NumberField::new('startingBalance');
        $endingBalance = NumberField::new('endingBalance');
        $totalDebit = NumberField::new('TotalDebit');
        $totalCredit = NumberField::new('totalCredit');
        $remoteFile = TextField::new('remoteFile');
        $source = AssociationField::new('source');
        $transactions = AssociationField::new('transactions');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $startingDate, $endingDate, $startingBalance, $endingBalance, $totalDebit];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $startingDate, $endingDate, $startingBalance, $endingBalance, $totalDebit, $totalCredit, $remoteFile, $source, $transactions];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $startingDate, $endingDate, $startingBalance, $endingBalance, $totalDebit, $totalCredit, $remoteFile, $source, $transactions];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $startingDate, $endingDate, $startingBalance, $endingBalance, $totalDebit, $totalCredit, $remoteFile, $source, $transactions];
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('source')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ;
    }
}
