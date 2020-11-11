<?php

namespace App\Controller\Admin;

use App\Entity\Budget;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Twig\BudgetExtension;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FormFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'name'])
            ->overrideTemplate('crud/index', 'admin/category/list.html.twig')
            ->addFormTheme('admin/field/category.html.twig')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('budget')
            ;
    }

    public function configureFields(string $pageName): iterable
    {   $budget = null;
        $name = TextField::new('name', 'category.name.label');
        $parent = AssociationField::new('parent', 'category.parent.label');
        $budget = AssociationField::new('budget', 'category.budget.label')->setFormTypeOption('disabled','disabled');
        $id = IntegerField::new('id', 'ID');
        $lft = IntegerField::new('lft');
        $lvl = IntegerField::new('lvl');
        $rgt = IntegerField::new('rgt');
        $root = AssociationField::new('root');
        $children = AssociationField::new('children');
        $detailsToCategories = AssociationField::new('detailsToCategories');
        $expenses = AssociationField::new('expenses');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$name, $parent, $budget, $lft, $rgt, $root];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $lft, $lvl, $rgt, $root, $parent, $children, $detailsToCategories, $expenses, $budget];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $parent, $budget];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $parent, $budget];
        }
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $session = $this->get('session');
        $budgetId = $session->get(BudgetExtension::BUDGET_ID_SESSION_KEY);
        $rootAlias = $queryBuilder->getRootAlias();
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->isNull($rootAlias.'.budget'),
                    $queryBuilder->expr()->eq($rootAlias.'.budget', ':budgetId')
                )
            )
            ->setParameter('budgetId', $budgetId)
        ;

        return $queryBuilder;
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = $this->get(FormFactory::class)->createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->setDynamicCategoryList($formBuilder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = $this->get(FormFactory::class)->createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->setDynamicCategoryList($formBuilder);
    }

    private function setDynamicCategoryList(FormBuilderInterface $formBuilder) : FormBuilderInterface
    {
        $formModifier = function (FormInterface $form, Budget $budget) {
            $form->add('parent', CategoryType::class, [
                'query_builder' => static function (NestedTreeRepository $er) use ($budget) {
                    return $er->getNodesHierarchyQueryBuilderByBudget($budget->getId());
                },
                'required' => false,
            ]);
        };

        // Add category field if budget is defined
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $form = $event->getForm();
            $expense = $event->getData();
            if ($expense->getBudget() instanceof Budget) {
                $formModifier($form, $expense->getBudget());
            }
        });

        $formBuilder->get('budget')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $budget = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $budget);
            }
        );

        return $formBuilder;
    }

    public function createEntity(string $entityFqcn) : Category
    {
        $entity = new $entityFqcn();

        $budgetId = $this->get('session')->get(BudgetExtension::BUDGET_ID_SESSION_KEY);
        $budget = $this->getDoctrine()->getRepository(Budget::class)->findOneBy(['id' => $budgetId]);

        $entity->setBudget($budget);

        return $entity;
    }
}
