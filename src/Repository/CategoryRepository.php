<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends NestedTreeRepository
{
    private ?int $budget;

    public function __construct(EntityManagerInterface $registry)
    {
        parent::__construct($registry, $registry->getClassMetadata(Category::class));
    }

    public function getTreeByBudget($budgetId = null, $node = null, $direct = false, array $options = array(), $includeNode = false)
    {
        $this->budget = $budgetId;

        return $this->childrenHierarchy($node, $direct, $options, $includeNode);
    }

    public function getRootNodesByBudget(int $budgetId, $sortByField = null, $direction = 'asc')
    {
        $qb = $this->getRootNodesQueryBuilderByBudget($budgetId, $sortByField, $direction);

        return $qb->getQuery()->getResult();
    }

    public function getRootNodesQueryBuilderByBudget(int $budgetId, $sortByField = null, $direction = 'asc')
    {
        $qb = $this->getRootNodesQueryBuilder($sortByField, $direction);

        $qb->andWhere('node.budget = :budget')
            ->setParameter('budget', $budgetId);

        return $qb;
    }

    /**
     * Overwrite of Gedmo\Tree\Entity\Repository\NestedTreeRepository::getNodesHierarchyQuery to filter categories on budget
     *
     * @param null $node
     * @param bool $direct
     * @param array $options
     * @param bool $includeNode
     *
     * @return \Doctrine\ORM\Query
     */
    public function getNodesHierarchyQuery($node = null, $direct = false, array $options = array(), $includeNode = false)
    {
        $qb = $this->getNodesHierarchyQueryBuilderByBudget($this->budget, $node, $direct, $options, $includeNode);

        return $qb->getQuery();
    }

    public function getNodesHierarchyQueryBuilderByBudget($budget = null, $node = null, $direct = false, array $options = array(), $includeNode = false)
    {
        $qb = $this->getNodesHierarchyQueryBuilder($node, $direct, $options, $includeNode);

        if (null !== $budget) {
            $qb->andWhere('node.budget = :budget')
                ->setParameter('budget', $budget);
        }

        return $qb;
    }
}
