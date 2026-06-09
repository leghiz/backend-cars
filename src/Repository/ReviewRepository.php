<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findPaginatedAndSorted(int $page, int $limit, string $dateOrder, string $ratingOrder): array
    {
        $dateDir = strtolower($dateOrder) === 'asc' ? 'ASC' : 'DESC';
        $ratingDir = strtolower($ratingOrder) === 'asc' ? 'ASC' : 'DESC';

        return $this->createQueryBuilder('r')
            // JOIN-ы для предотвращения N+1 запросов
            ->leftJoin('r.account', 'u')->addSelect('u')
            ->leftJoin('u.profile', 'p')->addSelect('p')
            ->leftJoin('r.lot', 'l')->addSelect('l')
            ->leftJoin('l.modification', 'm')->addSelect('m')
            ->leftJoin('m.model', 'md')->addSelect('md')
            ->leftJoin('md.manufacturer', 'man')->addSelect('man')
            // Используйте имя свойства сущности (например, createdAt), а не имя колонки в БД
            ->orderBy('r.rating', $ratingDir)
            ->addOrderBy('r.createdAt', $dateDir)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Review[] Returns an array of Review objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Review
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
