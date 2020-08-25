<?php

namespace App\Controller\Admin;

use App\Admin\Filter\CategoryFilter;
use App\Entity\Budget;
use App\Entity\Expense;
use App\Entity\Transaction;
use App\Form\Filter\CategoryWithChildrenFilterType;
use App\Twig\BudgetExtension;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Matleo\BankStatementParser\Model\CreditCardPayment;

class ExpenseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Expense::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new $entityFqcn();
        $request = $this->get('request_stack')->getCurrentRequest();

        $transactionId = (int) $request->get('transaction');

        $budgetId = $request->getSession()->get(BudgetExtension::BUDGET_ID_SESSION_KEY);
        $budget = $this->getDoctrine()->getRepository(Budget::class)->findOneBy(['id' => $budgetId]);

        $entity->setBudget($budget);
        $entity->setDate(new \DateTimeImmutable());

        return $this->fillExpenseWithTransaction($transactionId, $entity);
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

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $session = $this->get('session');
        $budgetId = $session->get(BudgetExtension::BUDGET_ID_SESSION_KEY);
        $rootAlias = $queryBuilder->getRootAlias();
        $queryBuilder
            ->andWhere($rootAlias.'.budget = :budgetId')
            ->setParameter('budgetId', $budgetId)
        ;

        return $queryBuilder;
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

    private function fillExpenseWithTransaction(int $transactionId, Expense $entity)
    {
        $transaction = $this->getDoctrine()->getRepository(Transaction::class)->findOneBy(['id' => $transactionId]);

        if ($transaction instanceof Transaction) {

            // Does other expense exist for this transaction
            $expenses = $this->getDoctrine()->getRepository(Expense::class)->findBy(['transaction' => $transactionId]);

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