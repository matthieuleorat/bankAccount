<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Debt;
use App\Entity\Expense;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @Route("/seeBalance", name="see_balance")
     *
     * @return Response
     */
    public function seeBalanceAction()
    {
        $this->em = $this->getDoctrine()->getManager();

        $users = $this->em->getRepository(User::class)->getUsersWithTheirDebt();

        // Construct an array with the user id as key
        $peoples = [];
        foreach ($users as $user) {
            $peoples[$user->getId()] = $user;
        }

        $assoc_arr = array_reduce($peoples, function ($result, User $user) {
            $result[$user->getId()] = 0;
            return $result;
        }, []);

        $debts = [];

        /** @var User $people */
        foreach ($users as $people) {
            $debtorKey = $people->getId();
            $debts[$debtorKey] = $assoc_arr;
            /** @var Debt $debt */
            foreach ($people->getDebts() as $debt) {
                $creditorKey = $debt->getCreditor()->getId();
                $debts[$debtorKey][$creditorKey] += $debt->getAmount();
            }
        }

        $debtsBalance = [];
        foreach ($debts as $debtorId => $debtsByUser) {
            foreach ($debtsByUser as $creditorId => $amount) {
                $total = $debts[$debtorId][$creditorId] - $debts[$creditorId][$debtorId];
                if ($total > 0) {
                    $debtsBalance[$debtorId][$creditorId] = $total;
                }
            }
        }

        return $this->render('admin/debt/see_balance.html.twig', [
            'debts' => $debts,
            'peoples' => $peoples,
            'debtsBalance' => $debtsBalance,
        ]);
    }
}
