<?php declare(strict_types=1);

namespace App\Form\Filter;

use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionNotFullFilledWithExpenseFilterType extends AbstractType
{
    public function getParent() : string
    {
        return BooleanFilterType::class;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([

        ]);
    }
}