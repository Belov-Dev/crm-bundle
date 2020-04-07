<?php

namespace A2Global\CRMBundle\Repository;

use A2Global\CRMBundle\Entity\EntityZ;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method EntityZ|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityZ|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityZ[]    findAll()
 * @method EntityZ[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityZ::class);
    }

    public function findByName($name): ?EntityZ
    {
        return $this->findOneBy(['name' => StringUtility::normalize($name)]);
    }
    // /**
    //  * @return Entity[] Returns an array of Entity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Entity
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
