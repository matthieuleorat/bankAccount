<?php

namespace App\Controller\Admin;

use App\Entity\Debt;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class DebtCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Debt::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'amount', 'comment']);
    }

    /**
     * @param string $pageName
     *
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        $amount = NumberField::new('amount', 'debt.amount.label');
        $date = DateField::new('date', 'debt.date.label');
        $debtor = AssociationField::new('debtor', 'debt.debtor.label');
        $creditor = AssociationField::new('creditor', 'debt.creditor.label');
        $isFulfilled = Field::new('isFulfilled');
        $comment = TextareaField::new('comment');
        $expense = AssociationField::new('expense', 'debt.expense.label');
        $id = IntegerField::new('id', 'ID');
        $isFulfilled = TextareaField::new('is_fulfilled', 'debt.isFulfilled.label');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$amount, $date, $debtor, $creditor, $expense, $isFulfilled];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $amount, $comment, $date, $isFulfilled, $debtor, $creditor, $expense];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$amount, $date, $debtor, $creditor, $isFulfilled, $comment, $expense];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$amount, $date, $debtor, $creditor, $isFulfilled, $comment, $expense];
        }
    }
}
