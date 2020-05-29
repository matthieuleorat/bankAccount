<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Budget;
use App\Entity\Category;
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

        $budgetId = $this->request->query->get('budget');
        /** @var Budget $budget */
        //$budget = $this->em->getRepository(Budget::class)->findOneBy(['id' => 1]);
        //dump($this->request->query->get('budget'));
        $test = $repo->getTreeByBudget(
            $budgetId,
            null,
            false,
            $options,
            true
        );

        return $this->render('admin/category/list.html.twig', [
            'htmlTree' => $test,
        ]);
    }
}
