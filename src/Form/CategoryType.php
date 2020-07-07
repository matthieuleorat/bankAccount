<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Twig\BudgetExtension;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $budget_id = $this->session->get(BudgetExtension::BUDGET_ID_SESSION_KEY);

        $resolver->setDefaults([
            'class' => Category::class,
            'query_builder' => static function (NestedTreeRepository $er) use ($budget_id) {
                return $er->getNodesHierarchyQueryBuilderByBudget($budget_id);
            },
            'choice_label' => static function(Category $choice) {
                return str_repeat('-', $choice->getLvl()). ' ' . $choice->getName();
            },
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'category_type';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'category_type';
    }
}
