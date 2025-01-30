<?php

namespace App\Controller;

use App\Entity\Tweets;
use App\Form\TweetsType;
use App\Repository\RetweetRepository;
use App\Repository\TweetsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


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
































    // Création d'un Retweet
    // #[Route('/{id}/retweet', name: 'app_tweet_retweet', methods: ['GET', 'POST'])]
    // public function retweet(Request $request, Tweets $tweet, EntityManagerInterface $entityManager): Response
    // {   
    //     // Vérification si l'user est connecté, sinon retour à la page login
    //     $user = $this->getUser(); 
    //     if (!$user) {
    //         return $this->redirectToRoute('app_login');
    //     }

    //     //Vérification que le formulaire a bien été envoyé avec post et récuperation du content)
    //     if ($request->isMethod('POST')) {
    //         $retweetContent = $request->request->get('retweet_content');

    //         // Création du retweet : Instancie un retweet / Liaison avec le user + le contenu du retweet et du tweet originel + date du retweet 
    //         $retweet = new Tweets();
    //         $retweet->setUser($user); 
    //         $retweet->setContent($retweetContent . "\n\n------\n" . $tweet->getContent());
    //         $retweet->setCreatedAt(new \DateTimeImmutable());
    //         $retweet->setUpdateAt(new \DateTimeImmutable());

    //         // Incrémentation du compteur Retweet
    //         $tweet->incrementRetweetCount();

    //         //Enregistrement dans la BDD + redirection a la page d'accueil
    //         $entityManager->persist($retweet);
    //         $entityManager->flush();
    //         return $this->redirectToRoute('app_tweets_index');
    //     }
        
    //     return $this->render('tweets/retweet.html.twig', [
    //         'tweet' => $tweet,
    //     ]);
    // }


    // Suppresion d'un retweet
    // #[Route('/retweet/{id}/delete', name: 'app_retweet_delete', methods: ['POST'])]
    // public function deleteRetweet(Tweets $retweet, EntityManagerInterface $entityManager): Response
    // {
    //     if (str_contains($retweet->getContent(), "--- Retweet ---")) {
    //         // Extraire le contenu du tweet original
    //         $originalContent = explode("--- Retweet ---", $retweet->getContent())[1] ?? null;

    //         if ($originalContent) {
    //             $originalTweet = $entityManager->getRepository(Tweets::class)
    //                 ->findOneBy(['content' => trim($originalContent)]);

    //             if ($originalTweet) {
    //                 $originalTweet->decrementRetweetCount();
    //             }
    //         }
    //     }

    //     $entityManager->remove($retweet);
    //     $entityManager->flush();

    //     return $this->redirectToRoute('app_tweets_index');
    // }
}
