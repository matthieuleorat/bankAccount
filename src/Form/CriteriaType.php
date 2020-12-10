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

use App\Entity\Criteria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CriteriaType extends AbstractType
{
    public const STATEMENT_DETAILS_FIELD = 'details';
    public const STATEMENT_DEBIT_FIELD = 'debit';
    public const STATEMENT_CREDIT_FIELD = 'credit';
    public const STATEMENT_DATE_FIELD = 'date';

    public const AVAILABLE_FIELD = [
        'Date dans le relevé' => self::STATEMENT_DATE_FIELD ,
        'Détails dans le relevé' => self::STATEMENT_DETAILS_FIELD,
        'Montant du débit' => self::STATEMENT_DEBIT_FIELD,
        'Montant du crédit' => self::STATEMENT_CREDIT_FIELD,
        'Mensualité de prêt' => [
            'Numéro de prêt' => 'type.loanNumber',
            'Montant du capital remboursé' => 'type.depreciatedCapital',
            'Montant des intérêt payés' => 'type.interest',
            "Montant de l'assurance payé" => 'type.insurance',
            'Capital restant' => 'type.remainingCapital',
            "Date de fin du prêt" => 'type.expectedEndDate',
        ],
        'Payment CB' => [
            'Numéro de carte' => 'type.cartId',
            'Date de paiement' => 'type.date',
            'Nom du commercant' => 'type.merchant',
        ],
        'Prélévement Européen' => [
            'Numéro de prélévement' => 'type.number',
            'De' => 'type.from',
            'Identifiant' => 'type.id',
            'Motif' => 'type.reason',
            'Référence' => 'type.ref',
            'Garrant' => 'type.warrant',
        ],
        'Virement permanent' => [
            'Pour' => 'type.recepient.',
            'Référence' => 'type.reference',
            'Motif' => 'type.reason',
            'Libéllé' => 'type.label',
        ],
        'Virement reçu' => [
            'Numéro' => 'type.number',
            'De' => 'type.from',
            'Motif' => 'type.reason',
            'Référence' => 'type.ref',
            'Id' => 'type.id',
        ],
        'Virement émis' => [
            'Numéro' => 'type.number',
            'Date' => 'type.date',
            'Compte' => 'type.account',
            'Pour' => 'type.for',
            'Référence' => 'type.ref',
            'Chez' => 'type.to',
            'Motif' => 'type.reason',
        ],
    ];

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('field', ChoiceType::class, [
                'choices' => self::AVAILABLE_FIELD,
            ])
            ->add('compareOperator', ChoiceType::class, [
                'choices' => [
                    'Est strictement égale' => 'equal',
                    'Commence par' => 'startWith',
                    'Se termine par' => 'endWith',
                    'Contient' => 'contain',
                ],
            ])
            ->add('value', TextType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => Criteria::class
        ]);
    }
}
