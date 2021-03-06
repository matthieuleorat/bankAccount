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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class CategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'class' => Category::class,
                'expanded' => true,
                'multiple' => false,
                'choice_attr' => static function (Category $choice, $key, $value) {
                    return [
                        'attr_lvl' => $choice->getLvl(),
                        'attr_children' => implode(
                            ',',
                            array_map(
                                static function (Category $category) {
                                    return $category->getId();
                                },
                                $choice->getChildren()->toArray()
                            )
                        ),
                    ];
                },
            ]
        );
    }

    public function getParent() : string
    {
        return EntityType::class;
    }

    public function getName() : string
    {
        return 'category_type';
    }

    public function getBlockPrefix() : string
    {
        return 'category_type';
    }
}
