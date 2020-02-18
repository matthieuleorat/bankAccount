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

        $startingDate = (new \DateTime('now'))->modify('-1 month');
        $endingDate = new \DateTimeImmutable('now');
        $interval = new \DateInterval('P1M');

        $form = $this->createForm(ResultatType::class, [
            'startingDate' => $startingDate,
            'endingDate' => $endingDate,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $startingDate = $form->getData()['startingDate'];
            $endingDate = $form->getData()['endingDate'];
        }

        $period = new \DatePeriod($startingDate, $interval, $endingDate);
        $p = [];
        foreach ($period as $key => $dt) {

            $p[] = [
                $key == 0 ? $startingDate : new \DateTime('first day of ' . $dt->format('F Y')),
                new \DateTime('last day of ' . $dt->format('F Y')) > $endingDate ? $endingDate : new \DateTime('last day of ' . $dt->format('F Y')),
            ];
        }

        $treeAsArray = $this->repo->childrenHierarchy();
        $test = [];
        foreach ($p as $periode) {
            $obj = new \stdClass();
            $obj->x = [];
            $obj->y = [];
            $obj->type = 'bar';
            $obj->name = $periode[0]->format('F Y');
            foreach ($treeAsArray as &$node) {
                $obj->x[] = $node['name'];
                $ids = $this->getChildren($node);
                $datas = $this->em->getRepository(Expense::class)->getTotalsForCategories($ids, $periode[0], $periode[1])[0];
                $obj->y[] = $datas['totalDebit'] - $datas['totalCredit'];
                $node['datas'][] = $datas;
            }
            $test[] = $obj;
        }

        return $this->render('admin/resultat/index.html.twig', [
            'form' => $form->createView(),
            'htmlTree' => $htmlTree,
            'treeAsArray' => $treeAsArray,
            'p' => $p,
            'test' => json_encode($test),
        ]);
    }

    /**
     * Recusrsive function
     *
     * @param array $category
     *
     * @return string
     */
    function getChildren(array $category) : string
    {
        $childrenId = '';
        if (array_key_exists('__children', $category)) {
            $childrenIdTmp = array_map(function(array $children) {
                return $children['id'] . ($this->getChildren($children) !== '' ? ','. $this->getChildren($children) : '');
            }, $category['__children']);
            $childrenId .=  implode(',', $childrenIdTmp);
        }

        return $childrenId;
    }
}