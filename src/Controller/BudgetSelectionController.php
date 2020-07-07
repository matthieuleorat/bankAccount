<?php declare(strict_types=1);

namespace App\Controller;

use App\Form\BudgetSelectionType;
use App\Twig\BudgetExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BudgetSelectionController extends AbstractController
{
    /**
     * @Route("/budget-selection", name="budget_selection")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function execute(Request $request) : RedirectResponse
    {
        $form = $this->createForm(BudgetSelectionType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $budget = $form->getData()['budget'];
            $this->container->get('session')->set(BudgetExtension::BUDGET_ID_SESSION_KEY, $budget->getId());
        }

        $redirecteTo = $request->server->get('HTTP_REFERER');
        if (preg_match('@(https://.*/admin/\?entity=Budget&action=show&id=)(\d+)(.*)@', $redirecteTo, $matches)) {
            $redirecteTo = $matches[1].$budget->getId().$matches[3];
        }

        return $this->redirect($redirecteTo);
    }
}