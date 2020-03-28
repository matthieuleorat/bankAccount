<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Debt;
use App\Entity\Expense;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;

class DebtController extends EasyAdminController
{
    public function createNewEntity()
    {
        $entityFullyQualifiedClassName = $this->entity['class'];
        $entity = new $entityFullyQualifiedClassName();

        $expenseId = (int) $this->request->query->get('expense_id');

        return $this->fillDebtWithExpense($expenseId, $entity);
    }

    public function fillDebtWithExpense(int $expenseId, Debt $entity) : Debt
    {
        $expense = $this->em->getRepository(Expense::class)->findOneBy(['id' => $expenseId]);

        if ($expense instanceof Expense) {
            $entity->setAmount($expense->getCredit() + $expense->getDebit());
            $entity->setComment($expense->getLabel());
            $entity->setDate($expense->getDate());
            $entity->addExpense($expense);
        }

        return $entity;
    }
}
