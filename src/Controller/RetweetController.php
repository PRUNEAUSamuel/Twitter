<?php

namespace App\Controller;

use App\Entity\Tweets;
use App\Entity\Retweet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/retweet')]

final class RetweetController extends AbstractController{
    #[Route(name: 'app_retweet')]
    public function index(): Response
    {
        return $this->render('retweet/index.html.twig', [
            'controller_name' => 'RetweetController',
        ]);
    }

    #[Route ('/{id}', name:'app_tweet_retweet', methods:['GET','POST'])]
    public function retweet(Tweets $tweets, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if(!$user) {
            return $this->redirectToRoute('app_login');
        }

        $retweet=new Retweet();
        $retweet->setUser($user);
        $retweet->setTweet($tweets);
        $retweet->setCreatedAt(new \DateTimeImmutable());



        if ($request -> isMethod('POST')) {
            $content = $request->request->get('retweet_content');

            if ($content) {
                $retweet -> setContent($content);
            }

            $entityManager -> persist($retweet);

            $tweets->incrementRetweetCount();
            $entityManager->flush();
            
            return $this->redirectToRoute('app_tweets_index');
        }

        return $this->render('tweets/retweet.html.twig',[ 'tweet'=>$tweets,
        ]);
    }

}
