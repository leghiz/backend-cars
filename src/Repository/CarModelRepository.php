<?php

namespace App\Repository;

use App\Entity\CarModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarModel>
 */
class CarModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarModel::class);
    }

    public function findByIdOrNameAndManufacturer(string $input, Manufacturer $manufacturer): ?CarModel
    {
        if (is_numeric($input)) {
            return $this->find((int)$input);
        }

        return $this->createQueryBuilder('cm')
            ->where('LOWER(cm.name) = LOWER(:name)')
            ->andWhere('cm.manufacturer = :manufacturer')
            ->setParameter('name', $input)
            ->setParameter('manufacturer', $manufacturer)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return CarModel[] Returns an array of CarModel objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CarModel
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
