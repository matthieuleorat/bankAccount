<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Expense;
use App\Form\ResultatType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ResultatController extends EasyAdminController
{
    /**
     * @var \Doctrine\Persistence\ObjectRepository
     */
    private $repo;

    /**
     * @Route("/resultat", name="resultat")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $this->em = $this->getDoctrine()->getManager();
        $this->repo = $this->em->getRepository(Category::class);

        $htmlTree = $this->repo->childrenHierarchy(
            null, /* starting from root nodes */
            false, /* true: load all children, false: only direct */
            array(
                'decorate' => true,
                'representationField' => 'slug',
                'html' => true
            )
        );

        $startingDate = new \DateTimeImmutable('2019-12-01');
        $endingDate = new \DateTimeImmutable('2020-02-29');
        $interval = new \DateInterval('P1M');

        $period = new \DatePeriod($startingDate, $interval, $endingDate);
        $p = [];
        foreach ($period as $dt) {
            $p[] = [
                new \DateTimeImmutable('first day of ' . $dt->format('F Y')),
                new \DateTimeImmutable('last day of ' . $dt->format('F Y')),
            ];
        }

        $treeAsArray = $this->repo->childrenHierarchy();

        foreach ($p as $periode) {
            foreach ($treeAsArray as &$node) {
                // Get expenses associated with current node
                $node['datas'][] = $this->em->getRepository(Expense::class)->getTotalsForCategory($node['id'], $periode[0], $periode[1])[0];

                foreach ($node['__children'] as &$child) {
                    $child['datas'][] = $this->em->getRepository(Expense::class)->getTotalsForCategory($child['id'], $periode[0], $periode[1])[0];

                    foreach ($child['__children'] as &$subchild) {
                        $subchild['datas'][] = $this->em->getRepository(Expense::class)->getTotalsForCategory($subchild['id'], $periode[0], $periode[1])[0];
                    }
                }
            }
        }

        $form = $this->createForm(ResultatType::class, null);

        $form->handleRequest($request);

        return $this->render('admin/resultat/index.html.twig', [
            'form' => $form->createView(),
            'htmlTree' => $htmlTree,
            'treeAsArray' => $treeAsArray,
            'p' => $p
        ]);
    }

    function getChildren(Category $category)
    {
        $children = $this->repo->getChildren($category, true);

        foreach ($children as $child) {
            $this->getChildren($category);
        }
    }
}