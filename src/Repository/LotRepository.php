<?php

namespace App\Repository;

use App\Entity\Lot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lot>
 */
class LotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lot::class);
    }

    /**
     * @return Lot[]
     */
    public function findByFilters(
        int $page,
        int $limit,
        ?string $search = null,
        ?int $manufacturerId = null,
        ?int $modelId = null,
        ?int $colorId = null,
        ?string $transmission = null,
        ?string $drive = null,
        ?int $year = null,
        ?float $priceFrom = null,
        ?float $priceTo = null,
        ?int $mileageFrom = null,
        ?int $mileageTo = null,
        ?int $engineVolumeId = null,
        ?bool $isSold = null
    ): array {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.modification', 'm')
            ->leftJoin('m.model', 'cm')
            ->leftJoin('cm.manufacturer', 'man')
            ->leftJoin('m.engine_volume', 'ev')
            ->leftJoin('l.background', 'b');

        if ($priceFrom !== null) {
            $qb->andWhere('l.price >= :priceFrom')->setParameter('priceFrom', $priceFrom);
        }
        if ($priceTo !== null) {
            $qb->andWhere('l.price <= :priceTo')->setParameter('priceTo', $priceTo);
        }
        if ($isSold !== null) {
            $qb->andWhere('l.isSold = :isSold')->setParameter('isSold', $isSold);
        }
        if ($manufacturerId !== null) {
            $qb->andWhere('man.id = :manufacturerId')->setParameter('manufacturerId', $manufacturerId);
        }
        if ($modelId !== null) {
            $qb->andWhere('cm.id = :modelId')->setParameter('modelId', $modelId);
        }
        if ($colorId !== null) {
            $qb->leftJoin('b.color', 'col')
                ->andWhere('col.id = :colorId')->setParameter('colorId', $colorId);
        }
        if ($engineVolumeId !== null) {
            $qb->andWhere('ev.id = :engineVolumeId')->setParameter('engineVolumeId', $engineVolumeId);
        }
        if ($transmission !== null) {
            $qb->andWhere('m.transmission = :transmission')->setParameter('transmission', $transmission);
        }
        if ($drive !== null) {
            $qb->andWhere('m.drive = :drive')->setParameter('drive', $drive);
        }

        if ($search !== null && trim($search) !== '') {
            $qb->andWhere('(LOWER(man.name) LIKE :search OR LOWER(cm.name) LIKE :search)')
                ->setParameter('search', '%' . mb_strtolower(trim($search)) . '%');
        }

        if ($year !== null) {
            $qb->andWhere('m.production_year >= :yearStart')
                ->andWhere('m.production_year < :yearEnd')
                ->setParameter('yearStart', new \DateTime($year . '-01-01'))
                ->setParameter('yearEnd', new \DateTime(($year + 1) . '-01-01'));
        }

        if ($mileageFrom !== null) {
            $qb->andWhere('b.mileage >= :mileageFrom')->setParameter('mileageFrom', $mileageFrom);
        }
        if ($mileageTo !== null) {
            $qb->andWhere('b.mileage <= :mileageTo')->setParameter('mileageTo', $mileageTo);
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Lot[] Returns an array of Lot objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Lot
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
