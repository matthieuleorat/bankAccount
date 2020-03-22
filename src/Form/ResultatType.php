<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Gedmo\Tree\Entity\Repository\AbstractTreeRepository;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResultatType extends AbstractType
{
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
                'query_builder' => static function (NestedTreeRepository $er) {
                    return $er->getNodesHierarchyQueryBuilder();
                },
                'choice_attr' => static function(Category $choice, $key, $value) {
                    return [
                        'attr_lvl' => $choice->getLvl(),
                        'attr_children' => implode(',', array_map(static function(Category $category) {
                            return $category->getId();
                        }, $choice->getChildren()->toArray())),
                    ];
                },
            ])

            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn btn-primary action-save']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}