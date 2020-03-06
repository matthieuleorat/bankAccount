<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Filter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('field', TextType::class, [])
            ->add('compareOperator', ChoiceType::class, [
                'choices' => [
                    'Est strictement égale' => 'equal',
                    'Commence par' => 'startWith',
                    'Se termine par' => 'endWith',
                    'Contient' => 'contain',
                ]
            ])
            ->add('value', TextType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Filter::class
        ]);
    }
}
