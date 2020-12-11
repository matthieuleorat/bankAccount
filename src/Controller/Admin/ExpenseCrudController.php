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

use App\Admin\Field\CategoryField;
use App\Admin\Filter\CategoryFilter;
use App\Entity\Budget;
use App\Entity\Expense;
use App\Entity\Transaction;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Twig\BudgetExtension;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FormFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use BankStatementParser\Model\CreditCardPayment;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
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
            ->addFormTheme('admin/field/category.html.twig');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addJsFile('build/dynamic-category-list.js');
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $queryBuilder = $this->get(EntityRepository::class)->createQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        );

        $session = $this->get('session');
        $budgetId = $session->get(BudgetExtension::BUDGET_ID_SESSION_KEY);
        $rootAlias = $queryBuilder->getRootAlias();
        $queryBuilder
            ->andWhere($rootAlias.'.budget = :budgetId')
            ->setParameter('budgetId', $budgetId);

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        $label = TextareaField::new('label')->setTemplatePath('admin/expense/list/details.html.twig');
        $category = CategoryField::new('category');
        $debit = NumberField::new('debit');
        $credit = NumberField::new('credit');
        $date = DateField::new('date');
        $id = IntegerField::new('id', 'ID');
        $comment = TextareaField::new('comment');
        $transaction = AssociationField::new('transaction');
        $budget = AssociationField::new('budget');
        $debts = AssociationField::new('debts');
        $amount = TextareaField::new('amount')->setTemplatePath(
            'admin/transaction/list/amount.html.twig'
        );

        if (Crud::PAGE_INDEX === $pageName) {
            return [$date, $label, $category, $amount, $budget];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $label, $debit, $credit, $date, $comment, $category, $transaction, $budget, $debts];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$label, $credit, $budget, $debit, $date];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$label, $budget, $category, $debit, $credit, $date];
        }

        return [];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CategoryFilter::new('category'))
            ->add('date')
            ->add('label');
    }

    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context
    ): FormBuilderInterface {
        $formBuilder = $this->get(FormFactory::class)->createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->setDynamicCategoryList($formBuilder);
    }

    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context
    ): FormBuilderInterface {
        $formBuilder = $this->get(FormFactory::class)->createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->setDynamicCategoryList($formBuilder);
    }

    private function setDynamicCategoryList(FormBuilderInterface $formBuilder) : FormBuilderInterface
    {
        $formModifier = function (FormInterface $form, Budget $budget) {
            $form->add(
                'category',
                CategoryType::class,
                [
                    'query_builder' => static function (CategoryRepository $categoryRepository) use ($budget) {
                        return $categoryRepository->getNodesHierarchyQueryBuilderByBudget($budget->getId());
                    },
                ]
            );
        };

        // Add category field if budget is defined
        $formBuilder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $form = $event->getForm();
                $expense = $event->getData();
                if ($expense->getBudget() instanceof Budget) {
                    $formModifier($form, $expense->getBudget());
                }
            }
        );

        $formBuilder->get('budget')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $budget = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $budget);
            }
        );

        return $formBuilder;
    }

    private function fillExpenseWithTransaction(int $transactionId, Expense $entity) : Expense
    {
        $transaction = $this->getDoctrine()->getRepository(Transaction::class)->findOneBy(
            ['id' => $transactionId]
        );

        if ($transaction instanceof Transaction) {
            // Does other expense exist for this transaction
            $expenses = $this->getDoctrine()->getRepository(Expense::class)->findBy(
                ['transaction' => $transactionId]
            );

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
