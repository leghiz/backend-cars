<?php

namespace App\Repository;

use App\Entity\EngineVolume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EngineVolume>
 */
class EngineVolumeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EngineVolume::class);
    }

    public function findByVolumeValue(float $volume): ?EngineVolume
    {
        $formatted = number_format($volume, 1, '.', '');

        $entity = $this->findOneBy(['volume' => $formatted]);
        if (!$entity) {
            $entity = $this->findOneBy(['volume' => $volume]);
        }

        return $entity;
    }

    //    /**
    //     * @return EngineVolume[] Returns an array of EngineVolume objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?EngineVolume
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
