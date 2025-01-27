<?php

namespace App\Repository;

use App\Entity\Tweets;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tweets>
 */
class TweetsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tweets::class);
    }


    public function findByUser(Users $user) {
        return $this->createQueryBuilder("tweet")
        ->leftJoin('tweet.content', 'content')
        ->where('tweet.user = :user')
        ->setParameter('user', $user)
        ->orderBy("tweet.createdAt", 'DESC')
        ->getQuery()
        ->getResult();
    }

//    /**
//     * @return Tweets[] Returns an array of Tweets objects
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

//    public function findOneBySomeField($value): ?Tweets
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
