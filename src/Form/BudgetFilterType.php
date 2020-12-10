<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Form;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BudgetFilterType extends AbstractType
{
    const OPTION_BUDGET_KEY = 'budget';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startingDate', DateType::class, [
                'widget' => 'single_text',
                'label' => "Date de début",
            ])
            ->add('endingDate', DateType::class, [
                'widget' => 'single_text',
                'label' => "Date de fin",
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
                'query_builder' => function (CategoryRepository $repo) use ($options) {
                    return $repo->getNodesHierarchyQueryBuilderByBudget($options[self::OPTION_BUDGET_KEY]);
                },
                'choice_attr' => static function (Category $choice, $key, $value) {
                    return [
                        'attr_lvl' => $choice->getLvl(),
                        'attr_children' => implode(',', array_map(static function (Category $category) {
                            return $category->getId();
                        }, $choice->getChildren()->toArray())),
                    ];
                },
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => ['class' => 'btn btn-primary action-save']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            self::OPTION_BUDGET_KEY => null,
        ]);
    }
}
