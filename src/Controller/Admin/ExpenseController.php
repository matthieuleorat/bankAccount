<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Expense;
use App\Entity\Transaction;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Matleo\BankStatementParser\Model\CreditCardPayment;

class ExpenseController extends EasyAdminController
{
    public function createNewEntity()
    {
        $entityFullyQualifiedClassName = $this->entity['class'];
        $entity = new $entityFullyQualifiedClassName();

        $transactionId = (int) $this->request->query->get('transaction');

        return $this->fillExpenseWithTransaction($transactionId, $entity);
    }

    private function fillExpenseWithTransaction(int $transactionId, Expense $entity)
    {
        $transaction = $this->em->getRepository(Transaction::class)->findOneBy(['id' => $transactionId]);

        if ($transaction instanceof Transaction) {

            // Does other expense exist for this transaction
            $expenses = $this->em->getRepository(Expense::class)->findBy(['transaction' => $transactionId]);

            $credit = $transaction->getCredit();
            $debit = $transaction->getDebit();

            foreach ($expenses as $expense) {
                $credit -= $expense->getCredit();
                $debit -= $expense->getDebit();
            }

            $entity->setCredit($credit);
            $entity->setDebit($debit);

            $label = $transaction->getDetails();
            $date = $transaction->getDate();

            if ($transaction->getType() instanceof CreditCardPayment) {
                $date = $transaction->getType()->getDate();
                $label = $transaction->getType()->getMerchant();
            }

            $entity->setDate($date);
            $entity->setLabel($label);

            $entity->setTransaction($transaction);

            if (null !== $defaultBudget = $transaction->getStatement()->getSource()->getDefaultBudget()) {
                $entity->setBudget($defaultBudget);
            }
        }

        return $entity;
    }
}
