<?php

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    //    /**
    //     * @return Invoice[] Returns an array of Invoice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Invoice
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Get last Invoice By User
     */
    public function getLastInvoiceId(User $user)
    {
        return $this->createQueryBuilder('i')
        ->innerJoin('i.address','add')
        ->innerJoin('add.user','use')
        ->orWhere('use = :user')
        ->select('max(i.id) as invoiceId')
        ->setParameter('user', $user)
        ->getQuery()
        ->getOneOrNullResult();
    }
    /**
     * Get Alle Invoices from yesterday & Today
     * they are not closed yet
     */
    public function today_yesterday_invoices($closed)
    {
        $yesterday = (new \DateTime())->modify('-1 day')->setTime(0, 0, 0);
        $tomorrow  = (new \DateTime())->modify('+1 day')->setTime(0, 0, 0);

        return $this->createQueryBuilder('i')
            ->where('i.date >= :yesterday')
            ->andWhere('i.date < :tomorrow')
            ->andWhere('i.closed = :closed')
            ->setParameter('yesterday', $yesterday)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('closed', $closed)
            ->getQuery()
            ->getResult();
    }
}
