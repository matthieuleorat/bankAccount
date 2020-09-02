<?php

namespace App\Controller\Admin;

use App\Entity\DetailsToCategory;
use App\Filtering\ApplyFilter;
use App\Filtering\AttributeExtractor;
use App\Filtering\CategoryGuesser;
use App\Twig\BudgetExtension;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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

    public function __construct(CategoryGuesser $categoryGuesser, AttributeExtractor $attributeExtractor, ApplyFilter $applyFilter)
    {
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
        $panel1 = FormField::addPanel('Filtre');
        $regex = TextField::new('regex');
        $budget = AssociationField::new('budget');
        $criteria = AssociationField::new('criteria');
        $panel2 = FormField::addPanel('Dépense');
        $label = TextField::new('label');
        $category = AssociationField::new('category');
        $debit = TextField::new('debit');
        $credit = TextField::new('credit');
        $date = TextField::new('date');
        $comment = TextareaField::new('comment');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $regex, $label, $debit, $credit, $date, $category];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $regex, $label, $debit, $credit, $date, $comment, $category, $criteria, $budget];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $regex, $budget, $criteria, $panel2, $label, $category, $debit, $credit, $date, $comment];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel1, $regex, $budget, $criteria, $panel2, $label, $category, $debit, $credit, $date, $comment];
        }
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
}
