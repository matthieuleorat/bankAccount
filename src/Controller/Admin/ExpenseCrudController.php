<?php

namespace App\Controller\Admin;

use App\Admin\Filter\CategoryFilter;
use App\Entity\Expense;
use App\Form\Filter\CategoryWithChildrenFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ExpenseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Expense::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Expense')
            ->setEntityLabelInPlural('Expense')
            ->setSearchFields(['id', 'label', 'debit', 'credit', 'comment'])
            ->overrideTemplate('crud/index', 'admin/expense/list.html.twig')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $label = TextareaField::new('label')->setTemplatePath('admin/expense/list/details.html.twig');
        $category = AssociationField::new('category');
        $debit = NumberField::new('debit');
        $credit = NumberField::new('credit');
        $date = DateField::new('date');
        $id = IntegerField::new('id', 'ID');
        $comment = TextareaField::new('comment');
        $transaction = AssociationField::new('transaction');
        $budget = AssociationField::new('budget');
        $debts = AssociationField::new('debts');
        $amount = TextareaField::new('amount')->setTemplatePath('admin/transaction/list/amount.html.twig');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$date, $label, $category, $amount, $budget];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $label, $debit, $credit, $date, $comment, $category, $transaction, $budget, $debts];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$label, $category, $debit, $credit, $date];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$label, $category, $debit, $credit, $date];
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CategoryFilter::new('category'))
            ->add('date')
            ->add('label')
        ;
    }
}
