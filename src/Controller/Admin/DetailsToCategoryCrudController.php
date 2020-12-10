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

use App\Entity\Budget;
use App\Entity\DetailsToCategory;
use App\Filtering\ApplyFilter;
use App\Filtering\AttributeExtractor;
use App\Filtering\CategoryGuesser;
use App\Form\CriteriaType;
use App\Repository\CategoryRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DetailsToCategoryCrudController extends AbstractCrudController
{
    /**
     * @var CategoryGuesser
     */
    private $categoryGuesser;
    /**
     * @var AttributeExtractor
     */
    private $attributeExtractor;
    /**
     * @var ApplyFilter
     */
    private $applyFilter;

    public function __construct(
        CategoryGuesser $categoryGuesser,
        AttributeExtractor $attributeExtractor,
        ApplyFilter $applyFilter
    ) {
        $this->categoryGuesser = $categoryGuesser;
        $this->attributeExtractor = $attributeExtractor;
        $this->applyFilter = $applyFilter;
    }

    public static function getEntityFqcn(): string
    {
        return DetailsToCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('DetailsToCategory')
            ->setEntityLabelInPlural('DetailsToCategory')
            ->setSearchFields(['id', 'regex', 'label', 'debit', 'credit', 'date', 'comment'])
            ->overrideTemplate('crud/index', 'admin/details-to-category/list.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $budgetId = $this->get('session')->get(BudgetExtension::BUDGET_ID_SESSION_KEY);

        $panel1 = FormField::addPanel('Filtre');
        $regex = TextField::new('regex');
        $budget = AssociationField::new('budget');
        $criteria = CollectionField::new('criteria')
            ->setFormTypeOption('allow_add', true)
            ->setFormTypeOption('allow_delete', true)
            ->setFormTypeOption('by_reference', false)
            ->setFormTypeOption('entry_type', 'App\Form\CriteriaType')
        ;
        $panel2 = FormField::addPanel('Dépense');
        $label = ChoiceField::new('label')
            ->setChoices(CriteriaType::AVAILABLE_FIELD)
            ->autocomplete();

        $category = AssociationField::new('category')
            ->setFormTypeOption(
                'query_builder',
                function (CategoryRepository $er) use ($budgetId) {
                    return $er->getNodesHierarchyQueryBuilderByBudget($budgetId);
                }
            );

        $debit = ChoiceField::new('debit')
            ->setChoices(CriteriaType::AVAILABLE_FIELD)
            ->autocomplete();

        $credit = ChoiceField::new('credit')
            ->setChoices(CriteriaType::AVAILABLE_FIELD)
            ->autocomplete();

        $date = ChoiceField::new('date')
            ->setChoices(CriteriaType::AVAILABLE_FIELD)
            ->autocomplete();

        $comment = TextareaField::new('comment');

        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $regex, $category, $budget];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $regex, $label, $debit, $credit, $date, $comment, $category, $criteria, $budget];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $regex, $budget, $criteria, $panel2, $label, $category, $debit, $credit, $date, $comment];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $regex, $budget, $criteria, $panel2, $label, $category, $debit, $credit, $date, $comment];
        }

        return [];
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
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->isNull($rootAlias.'.budget'),
                    $queryBuilder->expr()->eq($rootAlias.'.budget', ':budgetId')
                )
            )
            ->setParameter('budgetId', $budgetId);

        return $queryBuilder;
    }

    public function configureActions(Actions $actions): Actions
    {
        $apply = Action::new('apply', 'apply')
            ->linkToCrudAction('apply');

        return $actions
            ->add(Crud::PAGE_INDEX, $apply);
    }

    public function apply(AdminContext $context) : RedirectResponse
    {
        $request = $context->getRequest();

        try {
            $id = $request->get('entityId');
            /** @var DetailsToCategory $entity */
            $entity = $this->getDoctrine()->getRepository(DetailsToCategory::class)->find($id);

            $expenses = $this->applyFilter->execute($entity);

            $this->addFlash('success', count($expenses) . ' transactions trouvées');
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
        }

        $crudUrlGenerator = $this->get(CrudUrlGenerator::class);
        $url = $crudUrlGenerator->build()
            ->setController(DetailsToCategoryCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('budget');
    }


    public function createEntity(string $entityFqcn) : DetailsToCategory
    {
        $entity = new $entityFqcn();

        $entity->setLabel(CriteriaType::STATEMENT_DETAILS_FIELD);
        $entity->setCredit(CriteriaType::STATEMENT_CREDIT_FIELD);
        $entity->setDebit(CriteriaType::STATEMENT_DEBIT_FIELD);
        $entity->setDate(CriteriaType::STATEMENT_DATE_FIELD);

        $budgetId = $this->get('session')->get(BudgetExtension::BUDGET_ID_SESSION_KEY);
        $budget = $this->getDoctrine()->getRepository(Budget::class)->findOneBy(['id' => $budgetId]);

        $entity->setBudget($budget);

        return $entity;
    }
}
