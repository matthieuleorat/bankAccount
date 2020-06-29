<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\BudgetSelectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BudgetSelectionController extends AbstractController
{
    /**
     * @Route("/budget-selection", name="budget_selection")
     *
     * @param Request $request
     */
    public function execute(Request $request)
    {
        $form = $this->createForm(BudgetSelectionType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $budget = $form->getData()['budget'];
            $this->container->get('session')->set('budget_id', $budget->getId());
        }

        $referer = $request->server->get('HTTP_REFERER');

        return $this->redirect($referer);
    }
}