<?php

namespace App\Controller;

use App\Entity\Tweets;
use App\Entity\Likes;
use App\Entity\Retweet;
use App\Form\TweetsType;
use App\Repository\RetweetRepository;
use App\Repository\TweetsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/tweets')]
final class TweetsController extends AbstractController
{
    #[Route(name: 'app_tweets_index', methods: ['GET'])]
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
            'tweets' => $tweetsRepository->findBy([], ['createdAt' => 'DESC']),
            'retweets' => $retweetRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }



    // Ajouter un tweet
    #[Route('/profile/tweet/new', name: 'app_tweet_new', methods: ['GET', 'POST'])]
    public function newTweet(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, rediriger vers la page de login
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $tweet = new Tweets();
        $tweet->setUser($this->getUser()); // Lier le tweet à l'utilisateur connecté

        $form = $this->createForm(TweetsType::class, $tweet);
        $form->handleRequest($request);

        if (strlen($tweet->getContent()) > 255) {
            $form->get('content')->addError(new FormError("Le contenu du tweet ne peut pas dépasser 255 caractères."));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tweet);
            $entityManager->flush();
            return $this->redirectToRoute('app_tweets_index');
        }

        return $this->render('tweets/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Modifier un tweet
    #[Route('/profile/tweet/{id}/edit', name: 'app_tweet_edit', methods: ['GET', 'POST'])]
    public function editTweet(Request $request, Tweets $tweet, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est le propriétaire du tweet
        if ($this->getUser() !== $tweet->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        $form = $this->createForm(TweetsType::class, $tweet);
        $form->handleRequest($request);

        if (strlen($tweet->getContent()) > 255) {
            $form->get('content')->addError(new FormError("Le contenu du tweet ne peut pas dépasser 255 caractères."));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_tweets_index');
        }

        return $this->render('tweets/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //Confirmation de la suppression d'un tweet

    #[Route('/profile/tweet/{id}/confirm-delete', name: 'app_tweet_confirm_delete', methods: ['GET'])]

    public function confirmDelete(Tweets $tweet): Response
    {

        if ($this->getUser() !== $tweet->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('tweets/confirm_delete.html.twig', ['tweet' => $tweet]);
    }

    // Supprimer un tweet après confirmation 
    #[Route('/profile/tweet/{id}/delete', name: 'app_tweet_delete', methods: ['POST'])]
    public function deleteTweet(Request $request, Tweets $tweet, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est le propriétaire du tweet
        if ($this->getUser() !== $tweet->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        if ($this->isCsrfTokenValid('delete' . $tweet->getId(), $request->get('_token'))) {
            $entityManager->remove($tweet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_profile');
    }

#[Route("/like/{tweetId}", name:"like_tweet", methods:["POST"])]
public function like(int $tweetId, TweetsRepository $tweetRepository, Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $user = $this->getUser();  // L'utilisateur connecté
    $tweet = $tweetRepository->find($tweetId);

    if (!$tweet) {
        return new JsonResponse(['status' => 'error', 'message' => 'Le tweet n\'existe pas.'], 400);
    }

    $existingLike = $tweet->getLikes()->filter(function($like) use ($user) {
        return $like->getUser() === $user;
    })->first();

    if ($existingLike) {
        // Retirer le like (dislike)
        $entityManager->remove($existingLike);
        $entityManager->flush();
        $entityManager->refresh($tweet);

        $likeCount = $tweet->getLikes()->count();

        return new JsonResponse([
            'status' => 'success',
            'likesCount' => $likeCount,
            'liked' => false, 
        ]);
    }

    $like = new Likes();
    $like->setUser($user);
    $like->setTweet($tweet);

    $entityManager->persist($like);
    $entityManager->flush();
    $entityManager->refresh($tweet);


    return new JsonResponse([
        'status' => 'success',
        'likesCount' => $tweet->getLikes()->count(),
        'liked' => true,
    ]);
}

}
