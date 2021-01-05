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

use App\Entity\Source;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class ImportStatementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add(
                'account',
                EntityType::class,
                [
                    'class' => Source::class,
                    'label' => 'import_file.account.label',
                ]
            )
            ->add(
                'statement',
                FileType::class,
                [
                    'label' => 'import_file.file.label',
                    'multiple' => false,
                    'constraints' => [
                        new File(
                            [
                                'mimeTypes' => ['application/pdf', 'application/x-pdf',],
                                'mimeTypesMessage' => 'import_file.error.wrong_mime_type',
                            ]
                        )
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'attr' => ['class' => 'btn btn-primary action-save'],
                    'label' => 'import_file.submit.label',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([]);
    }
}
