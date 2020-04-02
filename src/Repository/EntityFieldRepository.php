<?php

namespace A2Global\CRMBundle\Repository;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method EntityField|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityField|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityField[]    findAll()
 * @method EntityField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityField::class);
    }


    public function findByName($name): ?EntityField
    {
        return $this->findOneBy(['name' => StringUtility::normalize($name)]);
    }

    // /**
    //  * @return EntityField[] Returns an array of EntityField objects
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
    public function findOneBySomeField($value): ?EntityField
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
