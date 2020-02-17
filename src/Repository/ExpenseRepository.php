<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Expense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function getTotalsForCategory($category, \DateTimeImmutable $startingDate, \DateTimeImmutable $endingDate)
    {
        return $this->createQueryBuilder('e')
            ->select('SUM(e.debit) as totalDebit')
            ->addSelect('SUM(e.credit) as totalCredit')
            ->where('e.category = :val')
            ->andWhere('e.date >= :startingDate')
            ->andWhere('e.date <= :endingDate')
            ->setParameter('val', $category)
            ->setParameter('startingDate', $startingDate)
            ->setParameter('endingDate', $endingDate)
            ->getQuery()
            ->getResult()
            ;
    }
}
