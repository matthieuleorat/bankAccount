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

use App\Entity\Expense;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @return Transaction[] Returns an array of Transaction objects
     */
    public function findTransactionWithoutExpense()
    {
        $qb = $this->createQueryBuilder('sdf');

        return $this->createQueryBuilder('t')
            ->leftJoin(Expense::class,"e", 'with','t.id = e.transaction')
            ->groupBy('t.id')
            ->having(
                $qb->expr()->orX(
                    $qb->expr()->isNull('sum(e.credit)'),
                    $qb->expr()->lt('sum(e.credit)', 't.credit')
                )
            )
            ->andHaving(
                $qb->expr()->orX(
                    $qb->expr()->isNull('sum(e.debit)'),
                    $qb->expr()->lt('sum(e.debit)', 't.debit')
                )
            )
            // TODO Remove ignored transactions
            ->getQuery()
            ->getResult()
        ;
    }
}
