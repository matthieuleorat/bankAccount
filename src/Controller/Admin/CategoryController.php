<?php declare(strict_types=1);

namespace App\Controller\Admin;

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

        $htmlTree = $repo->childrenHierarchy(
            null, /* starting from root nodes */
            false, /* true: load all children, false: only direct */
            $options
        );

        return $this->render('admin/category/list.html.twig', [
            'htmlTree' => $htmlTree,
        ]);
    }
}
