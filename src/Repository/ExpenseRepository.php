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

use App\Entity\Budget;
use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function getTotalsForCategories(
        Budget $budget,
        array $categories,
        \DateTime $startingDate,
        \DateTime $endingDate
    ) {
        return $this->createQueryBuilder('e')
            ->select('SUM(e.debit) as totalDebit')
            ->addSelect('SUM(e.credit) as totalCredit')
            ->where('e.category IN (:val)')
            ->andWhere('e.date >= :startingDate')
            ->andWhere('e.date <= :endingDate')
            ->andWhere('e.budget = :budget')
            ->setParameter('val', $categories)
            ->setParameter('startingDate', $startingDate)
            ->setParameter('endingDate', $endingDate)
            ->setParameter('budget', $budget)
            ->getQuery()
            ->getResult();
    }
}
