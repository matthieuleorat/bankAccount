<?php

namespace App\Controller\Admin;

use App\Entity\Transaction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class TransactionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Transaction::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'details', 'debit', 'credit', 'comment', 'type']);
    }

    public function configureFields(string $pageName): iterable
    {
        $date = DateField::new('date', 'transaction.date');
        $details = TextareaField::new('details', 'transaction.details')->setTemplatePath('admin/transaction/list/details.html.twig');
        $debit = NumberField::new('debit', 'transaction.debit');
        $credit = NumberField::new('credit', 'transaction.credit');
        $createExpense = Field::new('createExpense', 'transaction.createexpense');
        $amount = TextareaField::new('amount', 'transaction.amount')->setTemplatePath('admin/transaction/list/amount.html.twig');
        $type = TextareaField::new('type', 'transaction.type')->setTemplatePath('admin/transaction/show/type.html.twig');
        $expenses = AssociationField::new('expenses', 'transaction.expenses')->setTemplatePath('admin/transaction/list/expenses.html.twig');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$date, $details, $expenses, $amount];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$date, $details, $amount, $type];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$date, $details, $debit, $credit, $createExpense];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$date, $details, $debit, $credit, $createExpense];
        }
    }
}
