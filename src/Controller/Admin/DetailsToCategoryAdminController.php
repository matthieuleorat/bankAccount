<?php

namespace App\Controller\Admin;

use App\Filtering\ApplyFilter;
use App\Filtering\AttributeExtractor;
use App\Filtering\CategoryGuesser;
use App\Entity\DetailsToCategory;
use App\Entity\Expense;
use App\Entity\Transaction;
use App\Form\CriteriaType;
use App\Twig\BudgetExtension;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DetailsToCategoryAdminController extends EasyAdminController
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

    public function __construct(CategoryGuesser $categoryGuesser, AttributeExtractor $attributeExtractor, ApplyFilter $applyFilter)
    {
        $this->categoryGuesser = $categoryGuesser;
        $this->attributeExtractor = $attributeExtractor;
        $this->applyFilter = $applyFilter;
    }

    public function createNewEntity() : DetailsToCategory
    {
        $entityFullyQualifiedClassName = $this->entity['class'];
        /** @var DetailsToCategory $entity */
        $entity = new $entityFullyQualifiedClassName();

        $entity->setLabel(CriteriaType::STATEMENT_DETAILS_FIELD);
        $entity->setCredit(CriteriaType::STATEMENT_CREDIT_FIELD);
        $entity->setDebit(CriteriaType::STATEMENT_DEBIT_FIELD);
        $entity->setDate(CriteriaType::STATEMENT_DATE_FIELD);

        return $entity;
    }

    public function applyAction() : RedirectResponse
    {
        try {
            $id = $this->request->query->get('id');
            /** @var DetailsToCategory $entity */
            $entity = $this->em->getRepository(DetailsToCategory::class)->find($id);

            $expenses = $this->applyFilter->execute($entity);

            $this->addFlash('success', count($expenses) . ' transactions trouvées');
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
        }

        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
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
            ->orWhere($rootAlias.'.budget is NULL')
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
}