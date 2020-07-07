<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Budget;
use App\Entity\Category;
use App\Twig\BudgetExtension;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends EasyAdminController
{
    protected function listAction() : Response
    {
        $repo = $this->em->getRepository(Category::class);

        $options = [
            'decorate' => true,
            'representationField' => 'slug',
            'html' => true,
            'nodeDecorator' => function($node) {
                return '<a href="'.$this->generateUrl(
                    'easyadmin', [
                        'entity' => 'Category',
                        'action' => 'edit',
                        'id' => $node['id']
                    ]
                ).'">'.$node['name'].'</a>';
            },
        ];

        $budgetId = $this->request->getSession()->get(BudgetExtension::BUDGET_ID_SESSION_KEY);

        $categoriesHtmlList = $repo->getTreeByBudget(
            $budgetId,
            null,
            false,
            $options,
            true
        );

        return $this->render('admin/category/list.html.twig', [
            'categoriesHtmlList' => $categoriesHtmlList,
        ]);
    }

    protected function createNewEntity() : Category
    {
        $entityFullyQualifiedClassName = $this->entity['class'];

        $entity = new $entityFullyQualifiedClassName();

        $budgetId = $this->request->getSession()->get(BudgetExtension::BUDGET_ID_SESSION_KEY);
        $budget = $this->getDoctrine()->getRepository(Budget::class)->findOneBy(['id' => $budgetId]);

        $entity->setBudget($budget);

        return $entity;
    }
}
