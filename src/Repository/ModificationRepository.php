<?php

namespace App\Repository;

use App\Entity\Modification;
use App\Entity\CarModel;
use App\Entity\EngineVolume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Modification>
 */
class ModificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Modification::class);
    }

    public function findOrCreate(
        CarModel $model,
        EngineVolume $volume,
        \DateTimeInterface $productionYear,
        string $transmission,
        string $drive
    ): Modification {
        $modification = $this->findOneBy([
            'model' => $model,
            'engine_volume' => $volume,
            'production_year' => $productionYear,
            'transmission' => $transmission,
            'drive' => $drive
        ]);

        if (!$modification) {
            $modification = new Modification();
            $modification->setModel($model);
            $modification->setEngineVolume($volume);
            $modification->setProductionYear($productionYear);
            $modification->setTransmission($transmission);
            $modification->setDrive($drive);

            $this->getEntityManager()->persist($modification);
        }

        return $modification;
    }

    //    /**
    //     * @return Modification[] Returns an array of Modification objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Modification
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
