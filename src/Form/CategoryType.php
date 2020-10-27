<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Category::class,
            'expanded' => true,
            'multiple' => false,
            'choice_attr' => static function(Category $choice, $key, $value) {
                return [
                    'attr_lvl' => $choice->getLvl(),
                    'attr_children' => implode(',', array_map(static function(Category $category) {
                        return $category->getId();
                    }, $choice->getChildren()->toArray())),
                ];
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
