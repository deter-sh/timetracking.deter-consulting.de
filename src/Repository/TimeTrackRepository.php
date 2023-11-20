<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\TimeTrack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeTrack>
 *
 * @method TimeTrack|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeTrack|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeTrack[]    findAll()
 * @method TimeTrack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeTrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeTrack::class);
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param Customer|null $customer
     * @return array|TimeTrack[]
     */
    public function findInDateRange(\DateTime $startDate, \DateTime $endDate, Customer $customer = null): array
    {

        $qb =  $this
            ->createQueryBuilder('t')
            ->andWhere('t.start <= :end')
            ->andWhere('t.start >= :start')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if ($customer) {
            $qb
                ->join('t.project', 'p')
                ->andWhere('p.customer = :customer')
                ->setParameter('customer', $customer);
        }

        return $qb
            ->orderBy('t.start', 'ASC')
            ->getQuery()
            ->getResult();

    }

//    /**
//     * @return TimeTrack[] Returns an array of TimeTrack objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TimeTrack
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findOpen(): ?TimeTrack
    {
        $qb =  $this
            ->createQueryBuilder('t');

        return $qb
            ->andWhere($qb->expr()->isNull('t.endtime'))
            ->andWhere('t.start >= :start')
            ->andWhere('t.start <= :end')
            ->setParameter('start', new \DateTime('today 00:00:00'))
            ->setParameter('end', new \DateTime('today 23:59:59'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

    }
}
