<?php

namespace App\Repository;

use App\Entity\Tweets;
use App\Entity\Retweet;
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



    // Fonction mise dans le repository plutot que dans le Controller pour regrouper tous les tweets et retweets

    public function findAllTweetsAndRetweets() : array
    {

        $tweets = $this->createQueryBuilder("tweet")
        -> orderBy('tweet.createdAt', 'DESC')
        ->getQuery()
        ->getResult();

        $retweets = $this->getEntityManager()->getRepository(Retweet::class) //Récupère la classe Retweet de RetweetRepository
        ->createQueryBuilder("retweet")
        -> orderBy('retweet.createdAt', 'DESC')
        ->getQuery()
        ->getResult();

        

        //On fusionne les deux tableaux et on les trie avec usort
        $allTweets = array_merge($tweets, $retweets);
        
        usort($allTweets, function($a,$b){
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });

        return $allTweets;
        
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
