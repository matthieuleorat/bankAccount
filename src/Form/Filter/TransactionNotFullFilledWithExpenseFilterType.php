<?php declare(strict_types=1);

/*
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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