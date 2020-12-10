<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Admin\Filter\Configurator;

use App\Admin\Filter\StatementFilter;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;

class StatementConfigurator implements FilterConfiguratorInterface
{
    public function supports(FilterDto $filterDto, ?FieldDto $fieldDto, EntityDto $entityDto, AdminContext $context): bool
    {
        return StatementFilter::class === $filterDto->getFqcn();
    }

    public function configure(FilterDto $filterDto, ?FieldDto $fieldDto, EntityDto $entityDto, AdminContext $context): void
    {
        $propertyName = $filterDto->getProperty();
        if (!$entityDto->isAssociation($propertyName)) {
            return;
        }

        $doctrineMetadata = $entityDto->getPropertyMetadata($propertyName);
        // TODO: add the 'em' form type option too?
        $filterDto->setFormTypeOptionIfNotSet('value_type_options.class', $doctrineMetadata->get('targetEntity'));
        $filterDto->setFormTypeOptionIfNotSet('value_type_options.multiple', $entityDto->isToManyAssociation($propertyName));
        $filterDto->setFormTypeOptionIfNotSet('value_type_options.attr.data-widget', 'select2');
        $filterDto->setFormTypeOption('value_type_options.query_builder', function (EntityRepository $er) {
            return $er->createQueryBuilder('s')
                ->orderBy('s.source', 'ASC')
                ->addOrderBy('s.startingDate', 'ASC');
        });
        
        if ($entityDto->isToOneAssociation($propertyName)) {
            // don't show the 'empty value' placeholder when all join columns are required,
            // because an empty filter value would always returns no result
            $numberOfRequiredJoinColumns = \count(array_filter($doctrineMetadata->get('joinColumns'), static function (array $joinColumnMapping): bool {
                return false === ($joinColumnMapping['nullable'] ?? false);
            }));

            $someJoinColumnsAreNullable = \count($doctrineMetadata->get('joinColumns')) !== $numberOfRequiredJoinColumns;

            if ($someJoinColumnsAreNullable) {
                $filterDto->setFormTypeOptionIfNotSet('value_type_options.placeholder', 'label.form.empty_value');
            }
        }
    }
}
