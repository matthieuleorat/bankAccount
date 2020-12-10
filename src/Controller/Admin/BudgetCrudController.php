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
use App\Entity\Category;
use App\Entity\Expense;
use App\Form\BudgetFilterType;
use App\Form\BudgetSelectionType;
use App\Repository\CategoryRepository;
use App\Repository\ExpenseRepository;
use App\Twig\BudgetExtension;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BudgetCrudController extends AbstractCrudController
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public static function getEntityFqcn(): string
    {
        return Budget::class;
    }

    public function detail(AdminContext $context)
    {
        $event = new BeforeCrudActionEvent($context);
        $this->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION)) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $this->container->get('session')->set(BudgetExtension::BUDGET_ID_SESSION_KEY, $context->getEntity()->getPrimaryKeyValue());

        $this->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_DETAIL)));
        $this->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());

        /** @var Category[] $categories */
        $categories = $this->categoryRepository->getRootNodesByBudget($context->getEntity()->getPrimaryKeyValue());

        $startingDate = new DateTime('first day of January');
        $endingDate = new DateTime('now');
        $form = $this->createForm(
            BudgetFilterType::class,
            [
                'startingDate' => $startingDate,
                'endingDate' => $endingDate,
                'categories' => $categories,
            ],
            [BudgetFilterType::OPTION_BUDGET_KEY => $context->getEntity()->getInstance()]
        );

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $startingDate = $form->getData()['startingDate'];
            $endingDate = $form->getData()['endingDate'];
            $categories = $form->getdata()['categories'];
        }

        $datas = $this->formatDatas($startingDate, $endingDate, $categories, $context->getEntity()->getInstance());

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_DETAIL,
            'templateName' => 'crud/detail',
            'entity' => $context->getEntity(),
            'form' => $form->createView(),
            'datasForGraph' => $datas['datasForGraph'],
            'datas' => $datas['datas'],
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;
    }

    public function selection() : RedirectResponse
    {
        $form = $this->createForm(BudgetSelectionType::class);

        $request = $this->get('request_stack')->getMasterRequest();

        $form->handleRequest($request);

        $redirecteTo = $request->server->get('HTTP_REFERER');

        if ($form->isSubmitted() && $form->isValid()) {
            $budget = $form->getData()['budget'];
            $this->container->get('session')->set(BudgetExtension::BUDGET_ID_SESSION_KEY, $budget->getId());
            if (preg_match('@(https://.*/admin\?crudAction=detail)(.*)@', $redirecteTo, $matches)) {
                $crudUrlGenerator = $this->get(CrudUrlGenerator::class);
                $redirecteTo = $crudUrlGenerator->build()
                    ->setController(BudgetCrudController::class)
                    ->setAction(CRUD::PAGE_DETAIL)
                    ->setEntityId($budget->getId())
                    ->generateUrl();
            }
        }

        return $this->redirect($redirecteTo);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Budget')
            ->setEntityLabelInPlural('Budget')
            ->setSearchFields(['id', 'name'])
            ->overrideTemplate('crud/detail', 'admin/budget/show.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name')->setTemplatePath('admin/budget/list/name.html.twig');
        $expenses = AssociationField::new('expenses');
        $categories = AssociationField::new('categories');
        $detailsToCategories = AssociationField::new('detailsToCategories');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$name];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $expenses, $categories, $detailsToCategories];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $expenses, $categories, $detailsToCategories];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $expenses, $categories, $detailsToCategories];
        }

        return [];
    }

    private function generatePeriodArray(
        DateTime $startingDate,
        DateTime $endingDate,
        string $granularity = 'monthly'
    ) : array {
        switch ($granularity) {
            default:
                $interval = new \DateInterval('P1M');
        }

        $period = new \DatePeriod($startingDate, $interval, $endingDate);

        $p = [];
        foreach ($period as $key => $dt) {
            $p[] = [
                $key == 0 ? $startingDate : new DateTime('first day of '.$dt->format('F Y')),
                new DateTime('last day of '.$dt->format('F Y')) > $endingDate ? $endingDate : new DateTime('last day of '.$dt->format('F Y')),
            ];
        }

        return $p;
    }

    private function formatDatas($startingDate, $endingDate, $categories, $budget)
    {
        $p = $this->generatePeriodArray($startingDate, $endingDate);

        $datas = new stdClass();
        $datas->headers = [];
        $datas->rows = [];
        $datas->totals = [];

        $datasForGraph = [];

        /** @var ExpenseRepository $expenseRepository */
        $expenseRepository = $this->getDoctrine()->getRepository(Expense::class);

        foreach ($p as $periode) {
            $obj = new stdClass();
            $obj->x = [];
            $obj->y = [];
            $obj->type = 'bar';
            $obj->name = $periode[0]->format('F Y');

            $row = new stdClass();
            $row->period = $periode;
            $row->values = [];
            $row->total = 0;
            $row->label = $periode[0]->format('F Y');

            foreach ($categories as $i => $category) {
                $obj->x[] = $category->getName();
                $ids = $this->categoryRepository->getChildren($category);
                $ids[] = $category;
                $values = $expenseRepository->getTotalsForCategories($budget, $ids, $periode[0], $periode[1])[0];
                $value = $values['totalCredit'] - $values['totalDebit'];
                if (false === array_key_exists($i, $datas->totals)) {
                    $datas->totals[$i] = 0;
                }
                $datas->totals[$i] += $value;
                $obj->y[] = $value;

                if (false === in_array($category, $datas->headers)) {
                    $datas->headers[] = $category;
                }
                $data = new stdClass();
                $data->category = $category;
                $data->value = $value;
                $row->total += $value;
                $row->values[] = $data;
            }
            $datas->rows[] = $row;

            $datasForGraph[] = $obj;
        }
        $datas->totals[] = array_sum($datas->totals);

        $parameters['datasForGraph'] = $datasForGraph;
        $parameters['datas'] = $datas;

        return $parameters;
    }
}
