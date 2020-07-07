<?php

namespace App\Twig;

use App\Entity\Budget;
use App\Form\BudgetSelectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BudgetExtension extends AbstractExtension
{
    const BUDGET_ID_SESSION_KEY = 'budget_id';
    /**
     * @var FormFactoryInterface
     */
    private FormFactoryInterface $factory;
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(FormFactoryInterface $factory, SessionInterface $session, EntityManagerInterface $em)
    {
        $this->factory = $factory;
        $this->session = $session;
        $this->em = $em;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('displayBudgetSelection', [$this, 'displayBudgetSelection'], ['is_safe' => ['html']]),
        ];
    }

    public function displayBudgetSelection()
    {
        $budget_id = $this->session->get(self::BUDGET_ID_SESSION_KEY);

        $budget = $this->em->getRepository(Budget::class)->findOneBy(['id' => $budget_id]);

        $form = $this->factory->create(BudgetSelectionType::class, ['budget' => $budget]);

        return $form->createView();
    }
}
