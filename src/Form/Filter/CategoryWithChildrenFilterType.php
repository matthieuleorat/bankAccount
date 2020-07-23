<?php declare(strict_types=1);

namespace App\Form\Filter;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryWithChildrenFilterType extends AbstractType
{
    public function getParent() : string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'class' => Category::class,
        ]);
    }
}
