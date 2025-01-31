<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\RetweetRepository;
use App\Repository\TweetsRepository;

final class MainController extends AbstractController{
    #[Route('/', name: 'home')]
    public function index(TweetsRepository $tweetsRepository,RetweetRepository $retweetRepository): Response
    {
        $tweets = $tweetsRepository->findBy([], ['createdAt' => 'DESC']);
        $retweets = $retweetRepository->findBy([], ['createdAt' => 'DESC']);

        $allTweets= array_merge($tweets, $retweets);

        usort($allTweets, function($a,$b){
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });


        return $this->render('tweets/index.html.twig', [
            'allTweets' => $allTweets,
           
        ]);
    }
}
