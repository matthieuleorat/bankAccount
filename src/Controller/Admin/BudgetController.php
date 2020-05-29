<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Expense;
use App\Form\ResultatType;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\HttpFoundation\Response;

class BudgetController extends EasyAdminController
{
    /**
     * The method that is executed when the user performs a 'show' action on an entity.
     *
     * @return Response
     */
    protected function showAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_SHOW);

        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        $fields = $this->entity['show']['fields'];
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);

        $this->dispatch(EasyAdminEvents::POST_SHOW, [
            'deleteForm' => $deleteForm,
            'fields' => $fields,
            'entity' => $entity,
        ]);

        $parameters = [
            'entity' => $entity,
            'fields' => $fields,
            'delete_form' => $deleteForm->createView(),
        ];

        $this->repo = $this->em->getRepository(Category::class);
        /** @var Category[] $categories */
        $categories = $this->repo->getRootNodesByBudget($entity->getId());

        $startingDate = new DateTime('first day of January');
        $endingDate = new DateTime('now');
        $form = $this->createForm(
            ResultatType::class,
            [
                'startingDate' => $startingDate,
                'endingDate' => $endingDate,
                'categories' => $categories,
            ],
            [ResultatType::OPTION_BUDGET_KEY => $entity],
        );

        if ($form->isSubmitted() && $form->isValid()) {
            $startingDate = $form->getData()['startingDate'];
            $endingDate = $form->getData()['endingDate'];
            $categories = $form->getdata()['categories'];
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
                $values = $this->em->getRepository(Expense::class)->getTotalsForCategories($entity, $ids, $periode[0], $periode[1])[0];
                $value =$values['totalCredit'] -  $values['totalDebit'];
                $obj->y[] =  $value;

                if (false === in_array($category, $datas->headers)) {
                    $datas->headers[] = $category;
                }
                $data = new \stdClass();
                $data->category = $category;
                $data->value = $value;
                $row->total += $value;
                $row->values[] = $data;
            }

            $datas->rows[] = $row;

            $datasForGraph[] = $obj;
        }

        $parameters['form'] = $form->createView();
        $parameters['datasForGraph'] = $datasForGraph;
        $parameters['datas'] = $datasForGraph;

        return $this->executeDynamicMethod(
            'render<EntityName>Template',
            ['show', $this->entity['templates']['show'], $parameters]
        );
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
                $key == 0 ? $startingDate : new DateTime('first day of ' . $dt->format('F Y')),
                new DateTime('last day of ' . $dt->format('F Y')) > $endingDate ? $endingDate : new DateTime('last day of ' . $dt->format('F Y')),
            ];
        }

        return $p;
    }
}
