<?php

namespace App\Controller\Admin;

use App\Filtering\ApplyFilter;
use App\Filtering\AttributeExtractor;
use App\Filtering\CategoryGuesser;
use App\Entity\DetailsToCategory;
use App\Entity\Expense;
use App\Entity\Transaction;
use App\Form\FilterType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
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

        $entity->setLabel(FilterType::STATEMENT_DETAILS_FIELD);
        $entity->setCredit(FilterType::STATEMENT_CREDIT_FIELD);
        $entity->setDebit(FilterType::STATEMENT_DEBIT_FIELD);
        $entity->setDate(FilterType::STATEMENT_DATE_FIELD);

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
}