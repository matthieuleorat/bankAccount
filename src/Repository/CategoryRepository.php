<?php

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class CategoryRepository extends NestedTreeRepository
{
    private ?int $budget;

    public function __construct(EntityManagerInterface $registry)
    {
        parent::__construct($registry, $registry->getClassMetadata(Category::class));
    }

    public function getRootNodesByBudget(int $budgetId, $sortByField = null, $direction = 'asc')
    {
        $qb = $this->getRootNodesQueryBuilderByBudget($budgetId, $sortByField, $direction);

        return $qb->getQuery()->getResult();
    }

    public function getRootNodesQueryBuilderByBudget(int $budgetId, $sortByField = null, $direction = 'asc')
    {
        $qb = $this->getRootNodesQueryBuilder($sortByField, $direction);

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull('node.budget'),
                $qb->expr()->eq('node.budget', ':budgetId')
            )
        )
            ->setParameter('budgetId', $budgetId);

        return $qb;
    }

    /**
     * Overwrite of Gedmo\Tree\Entity\Repository\NestedTreeRepository::getNodesHierarchyQuery
     *
     * @param object $node
     * @param bool $direct
     * @param array $options
     * @param bool $includeNode
     *
     * @return Query
     */
    public function getNodesHierarchyQuery(
        $node = null,
        $direct = false,
        array $options = [],
        $includeNode = false
    ) : Query {
        $qb = $this->getNodesHierarchyQueryBuilderByBudget($this->budget, $node, $direct, $options, $includeNode);

        return $qb->getQuery();
    }

    public function getNodesHierarchyQueryBuilderByBudget(
        $budget = null,
        $node = null,
        $direct = false,
        array $options = [],
        $includeNode = false
    ) : QueryBuilder {
        $qb = $this->getNodesHierarchyQueryBuilder($node, $direct, $options, $includeNode);

        if (null !== $budget) {
            $qb->andWhere('node.budget = :budget')
                ->setParameter('budget', $budget);
        }

        return $qb;
    }
}
