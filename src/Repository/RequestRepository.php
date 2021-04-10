<?php

namespace App\Repository;

use App\Entity\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Request|null find($id, $lockMode = null, $lockVersion = null)
 * @method Request|null findOneBy(array $criteria, array $orderBy = null)
 * @method Request[]    findAll()
 * @method Request[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }

    public function findRecently(string $address, string $ip): ?Request
    {
        $date = new \DateTime('-1 day', new \DateTimeZone('Utc'));
        return $this->createQueryBuilder('n')
            ->where('n.address = :address OR n.ip = :ip')
            ->setParameter('address', $address)
            ->setParameter('ip', $ip)
            ->andWhere('n.createdTs > :date')
            ->setParameter('date', $date)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
