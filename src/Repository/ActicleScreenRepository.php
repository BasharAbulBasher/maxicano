<?php

namespace App\Repository;

use App\Entity\ActicleScreen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActicleScreen>
 */
class ActicleScreenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActicleScreen::class);
    }

    //    /**
    //     * @return ActicleScreen[] Returns an array of ActicleScreen objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ActicleScreen
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

        public function visit($screenId)
        {
            return $this->createQueryBuilder('a')
                    ->join('a.article','art')
                    ->join('art.category','cat')
                    ->join('a.screen','scr')
                    ->select('cat.title as categoyTitle, art.title as title, art.description as description, art.price as price, art.image as image, scr.title as screenTitle ,scr.id as screenId, cat.id as categoryId')
                    ->andWhere('scr.id = :screenId')
                    ->setParameter('screenId', $screenId)
                    ->getQuery()
                   ->getResult();
        }
}
