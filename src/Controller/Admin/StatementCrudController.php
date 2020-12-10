<?php

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            ->setSearchFields(
                ['id', 'name', 'startingBalance', 'endingBalance', 'TotalDebit', 'totalCredit', 'remoteFile']
            );
    }

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
            return [
                $name,
                $startingDate,
                $endingDate,
                $startingBalance,
                $endingBalance,
                $totalDebit,
                $totalCredit,
                $remoteFile,
                $source,
                $transactions
            ];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [
                $name,
                $startingDate,
                $endingDate,
                $startingBalance,
                $endingBalance,
                $totalDebit,
                $totalCredit,
                $remoteFile,
                $source,
                $transactions
            ];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [
                $name,
                $startingDate,
                $endingDate,
                $startingBalance,
                $endingBalance,
                $totalDebit,
                $totalCredit,
                $remoteFile,
                $source,
                $transactions
            ];
        }

        return [];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('source');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
