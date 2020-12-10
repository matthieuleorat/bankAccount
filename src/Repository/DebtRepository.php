<?php

/*
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Repository;

use App\Entity\Debt;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Debt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Debt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Debt[]    findAll()
 * @method Debt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Debt::class);
    }

    public function findUnfulfilledDebtBetweenTwoUser(User $user1, User $user2)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.isFulfilled = :val')
            ->andWhere("d.debtor = :user1 AND d.creditor = :user2")
            ->orWhere("d.debtor = :user2 AND d.creditor = :user1")
            ->setParameter('val', false)
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Debt
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
