<?php

namespace App\Controller\Admin;

use App\Entity\Transaction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class TransactionWithoutExpenseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Transaction::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'details', 'debit', 'credit', 'comment', 'type']);
//            ->overrideTemplate('layout', 'admin/layout.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $date = DateField::new('date', 'transaction.date');
        $details = TextareaField::new('details', 'transaction.details')->setTemplatePath('admin/transaction/list/details.html.twig');
        $debit = NumberField::new('debit');
        $credit = NumberField::new('credit');
        $comment = TextareaField::new('comment');
        $ignore = Field::new('ignore');
        $type = TextareaField::new('type');
        $statement = AssociationField::new('statement');
        $expenses = AssociationField::new('expenses');
        $id = IntegerField::new('id', 'ID');
        $amount = TextareaField::new('amount', 'transaction.amount')->setTemplatePath('admin/transaction/list/amount.html.twig');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$date, $details, $amount];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $date, $details, $debit, $credit, $comment, $ignore, $type, $statement, $expenses];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$date, $details, $debit, $credit, $comment, $ignore, $type, $statement, $expenses];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$date, $details, $debit, $credit, $comment, $ignore, $type, $statement, $expenses];
        }
    }
}
