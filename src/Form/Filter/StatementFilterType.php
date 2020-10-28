<?php declare(strict_types=1);

namespace App\Form\Filter;

use App\Entity\Statement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatementFilterType extends AbstractType
{
    public function getParent() : string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'class' => Statement::class,
        ]);
    }
}
