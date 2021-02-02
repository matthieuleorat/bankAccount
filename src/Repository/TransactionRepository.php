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

use App\Entity\Expense;
use App\Entity\Transaction;
use BankStatementParser\Model\Operation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findFromOperation(Operation $operation, string $accountNumber) : ? Transaction
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.statement', 'statement')
            ->join('statement.source', 'source')
            ->where('t.date = :date')
            ->andWhere('source.number = :accountNumber')
            ->setParameter('date', $operation->getDate())
            ->setParameter('accountNumber', $accountNumber)
            ;

        if (true === $operation->isDebit()) {
            $qb->andWhere('t.debit = :debit')
                ->setParameter('debit', $operation->getDebit())
                ->andWhere('t.credit is NULL');
        }

        if (true === $operation->isCredit()) {
            $qb->andWhere('t.credit = :credit')
                ->setParameter('credit', $operation->getCredit())
                ->andWhere('t.debit is NULL');
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Transaction[] Returns an array of Transaction objects
     */
    public function findTransactionWithoutExpense()
    {
        $qb = $this->createQueryBuilder('sdf');

        return $this->createQueryBuilder('t')
            ->leftJoin(Expense::class, "e", 'with', 't.id = e.transaction')
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
            ->getResult();
    }
}
