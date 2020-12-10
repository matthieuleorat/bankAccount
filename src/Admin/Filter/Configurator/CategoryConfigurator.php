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

use App\Admin\Filter\CategoryFilter;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;

class CategoryConfigurator implements FilterConfiguratorInterface
{
    public $categoryRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->categoryRepository = $entityManager->getRepository(Category::class);
    }

    public function supports(
        FilterDto $filterDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto,
        AdminContext $context
    ): bool {
        return CategoryFilter::class === $filterDto->getFqcn();
    }

    public function configure(
        FilterDto $filterDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto,
        AdminContext $context
    ): void {
        $filterDto->setApplyCallable(
            function (
                QueryBuilder $queryBuilder,
                FilterDataDto $filterDataDto,
                ?FieldDto $fieldDto,
                EntityDto $entityDto
            ) use ($filterDto) {
                if (null === $queryBuilder->getParameter('categories')) {
                    $children = $this->categoryRepository->getChildren(
                        $filterDataDto->getValue(),
                        false,
                        null,
                        'asc',
                        true
                    );
                    $queryBuilder
                        ->andWhere(
                            sprintf(
                                '%s.%s IN (:categories)',
                                $queryBuilder->getRootAliases()[0],
                                $filterDataDto->getProperty()
                            )
                        )
                        ->setParameter('categories', $children);
                    $filterDto->apply($queryBuilder, $filterDataDto, $fieldDto, $entityDto);
                }
            }
        );
    }
}
