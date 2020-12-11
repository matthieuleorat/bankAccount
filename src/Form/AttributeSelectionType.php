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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class AttributeSelectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(
            ['choices' => CriteriaType::AVAILABLE_FIELD]
        );
    }

    public function getParent() : string
    {
        return ChoiceType::class;
    }
}
