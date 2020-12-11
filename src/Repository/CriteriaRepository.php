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

use App\Entity\Criteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Criteria|null find($id, $lockMode = null, $lockVersion = null)
 * @method Criteria|null findOneBy(array $criteria, array $orderBy = null)
 * @method Criteria[]    findAll()
 * @method Criteria[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class CriteriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Criteria::class);
    }
}
