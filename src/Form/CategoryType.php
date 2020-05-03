<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Category::class,
            'query_builder' => static function (NestedTreeRepository $er) {
                return $er->getNodesHierarchyQueryBuilder();
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
