<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Budget;
use App\Entity\Expense;
use App\Entity\Transaction;
use App\Twig\BudgetExtension;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Matleo\BankStatementParser\Model\CreditCardPayment;

class ExpenseController extends EasyAdminController
{
    public function createNewEntity()
    {
        $entityFullyQualifiedClassName = $this->entity['class'];
        $entity = new $entityFullyQualifiedClassName();

        $transactionId = (int) $this->request->query->get('transaction');

        $budgetId = $this->request->getSession()->get(BudgetExtension::BUDGET_ID_SESSION_KEY);
        $budget = $this->getDoctrine()->getRepository(Budget::class)->findOneBy(['id' => $budgetId]);

        $entity->setBudget($budget);
        $entity->setDate(new \DateTimeImmutable());

        return $this->fillExpenseWithTransaction($transactionId, $entity);
    }

    protected function listAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findByBudget($this->entity['class'], $this->request->query->get('page', 1), $this->entity['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'), $this->entity['list']['dql_filter']);

        $this->dispatch(EasyAdminEvents::POST_LIST, ['paginator' => $paginator]);

        $parameters = [
            'paginator' => $paginator,
            'fields' => $fields,
            'batch_form' => $this->createBatchForm($this->entity['name'])->createView(),
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['list', $this->entity['templates']['list'], $parameters]);
    }

    protected function findByBudget($entityClass, $page = 1, $maxPerPage = 15, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        if (null === $sortDirection || !\in_array(strtoupper($sortDirection), ['ASC', 'DESC'])) {
            $sortDirection = 'DESC';
        }

        $queryBuilder = $this->executeDynamicMethod('create<EntityName>ListQueryBuilder', [$entityClass, $sortDirection, $sortField, $dqlFilter]);

        $budgetId = $this->request->getSession()->get(BudgetExtension::BUDGET_ID_SESSION_KEY);
        $rootAlias = $queryBuilder->getRootAlias();
        $queryBuilder
            ->andWhere($rootAlias.'.budget = :budgetId')
            ->setParameter('budgetId', $budgetId)
        ;

        $this->filterQueryBuilder($queryBuilder);

        $this->dispatch(EasyAdminEvents::POST_LIST_QUERY_BUILDER, [
            'query_builder' => $queryBuilder,
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection,
        ]);

        return $this->get('easyadmin.paginator')->createOrmPaginator($queryBuilder, $page, $maxPerPage);
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
