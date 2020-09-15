<?php

namespace App\Controller\Admin;

use App\Admin\Field\ObjectType;
use App\Admin\Filter\TransactionNotFullFilledWithExpense;
use App\Entity\Transaction;
use Doctrine\Common\Collections\Collection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class TransactionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Transaction::class;
    }

    public function toggleIgnoreAction(AdminContext $context) : Response
    {
        $id = $context->getEntity()->getInstance()->getId();

        $em = $this->getDoctrine()->getManager();
        /** @var Transaction $entity */
        $entity = $em->getRepository(Transaction::class)->find($id);

        $value = true;

        if ($entity->isIgnore()) {
            $value = false;
        }

        $entity->setIgnore($value);

        $em->flush();

        $this->addFlash('success', 'Modification enregistrée');

        $url = $this->get(CrudUrlGenerator::class)->build()
            ->setAction(Action::INDEX)
            ->unset('entityId')
            ->generateUrl();

        return $this->redirect($url);
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
        $type = ObjectType::new('type', 'transaction.type')->setTemplatePath('admin/transaction/show/type.html.twig');
        $expenses = AssociationField::new('expenses', 'transaction.expenses')->setTemplatePath('admin/transaction/list/expenses.html.twig');
        $statement = AssociationField::new('statement');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$date, $details, $expenses, $amount, $statement];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$date, $details, $amount, $type];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$date, $details, $debit, $credit, $createExpense];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$date, $details, $debit, $credit, $createExpense];
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('ignore'))
            ->add(TransactionNotFullFilledWithExpense::new('expenses'))
            ->add('statement')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $crudUrlGenerator = $this->get(CrudUrlGenerator::class);

        $createExpense = Action::new('transactionToExpense', 'transaction.createexpense')
            ->linkToUrl(function (Transaction $entity) use ($crudUrlGenerator) {
                return $crudUrlGenerator->build()
                    ->setController(ExpenseCrudController::class)
                    ->setAction('new')
                    ->set('transaction', $entity->getId())
                    ->generateUrl();
            })
        ;

        $toggleIgnore = Action::new('toggleIgnore', 'transaction.toggleIngore')
            ->linkToUrl(function (Transaction $entity) use ($crudUrlGenerator) {
                return $crudUrlGenerator->build()
                    ->setController(TransactionCrudController::class)
                    ->setAction('toggleIgnore')
                    ->setEntityId($entity->getId())
                    ->generateUrl();
            })
            ->setTemplatePath('admin/transaction/list/action/ignore_transaction.html.twig')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $createExpense)
            ->add(Crud::PAGE_INDEX, $toggleIgnore)
            ;
    }
}
