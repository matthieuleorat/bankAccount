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

use App\Entity\DetailsToCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method DetailsToCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetailsToCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetailsToCategory[]    findAll()
 * @method DetailsToCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailsToCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetailsToCategory::class);
    }

    // /**
    //  * @return DetailsToCategory[] Returns an array of DetailsToCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DetailsToCategory
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
