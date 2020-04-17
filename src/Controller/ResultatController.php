<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\Expense;
use App\Form\ResultatType;
use App\Repository\CategoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ResultatController extends EasyAdminController
{
    /**
     * @var CategoryRepository|null
     */
    private $repo;

    /**
     * @Route("/resultat", name="resultat")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $this->em = $this->getDoctrine()->getManager();
        $this->repo = $this->em->getRepository(Category::class);

        $startingDate = (new \DateTime('first day of this month'))->modify('-3 month');
        $endingDate = new \DateTime('now');

        /** @var Category[] $categories */
        $categories = $this->repo->getRootNodes();

        $budget = $this->em->getRepository(Budget::class)->findOneBy(['id' => 1]);

        $form = $this->createForm(ResultatType::class, [
            'startingDate' => $startingDate,
            'endingDate' => $endingDate,
            'categories' => $categories,
            'budget' => $budget,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $startingDate = $form->getData()['startingDate'];
            $endingDate = $form->getData()['endingDate'];
            $categories = $form->getdata()['categories'];
            $budget = $form->getdata()['budget'];
        }

        $p = $this->generatePeriodArray($startingDate, $endingDate);

        $datas = new \stdClass();
        $datas->headers = [];
        $datas->rows = [];

        $datasForGraph = [];
        foreach ($p as $periode) {
            $obj = new \stdClass();
            $obj->x = [];
            $obj->y = [];
            $obj->type = 'bar';
            $obj->name = $periode[0]->format('F Y');

            $row = new \stdClass();
            $row->period = $periode;
            $row->values = [];
            $row->total = 0;

            foreach($categories as $category) {
                $obj->x[] = $category->getName();
                $ids = $this->repo->getChildren($category);
                $ids[] = $category;
                $values = $this->em->getRepository(Expense::class)->getTotalsForCategories($budget, $ids, $periode[0], $periode[1])[0];
                $obj->y[] = $values['totalDebit'] - $values['totalCredit'];

                if (false === in_array($category, $datas->headers)) {
                    $datas->headers[] = $category;
                }
                $data = new \stdClass();
                $data->category = $category;
                $data->value = $values['totalDebit'] - $values['totalCredit'];
                $row->total += $data->value;
                $row->values[] = $data;
            }

//            https://localhost/admin/?entity=Expense&action=list&filters[date][comparison]=between&filters[date][value][day]=1&filters[date][value][month]=1&filters[date][value][year]=2020&filters[date][value2][day]=31&filters[date][value2][month]=1&filters[date][value2][year]=2020
//            https://localhost/admin/?entity=Expense&action=list&filters[date][comparison]=between&filters[date][value][day]=1&filters[date][value][month]=1&filters[date][value][year]=2020&filters[date][value2][day]=31&filters[date][value2][month]=1&filters[date][value2][year]=2020
            $datas->rows[] = $row;

            $datasForGraph[] = $obj;
        }

        return $this->render('admin/resultat/index.html.twig', [
            'form' => $form->createView(),
            'datasForGraph' => $datasForGraph,
            'datas' => $datas,
        ]);
    }

    private function generatePeriodArray(
        \DateTime $startingDate,
        \DateTime $endingDate,
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
                $key == 0 ? $startingDate : new \DateTime('first day of ' . $dt->format('F Y')),
                new \DateTime('last day of ' . $dt->format('F Y')) > $endingDate ? $endingDate : new \DateTime('last day of ' . $dt->format('F Y')),
            ];
        }

        return $p;
    }
}