<?php

namespace App\Controller;

use App\Entity\Tweets;
use App\Form\TweetsType;
use App\Repository\TweetsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;


#[Route('/tweets')]
final class TweetsController extends AbstractController{
    #[Route(name: 'app_tweets_index', methods: ['GET'])]
    public function index(TweetsRepository $tweetsRepository): Response
    {
        return $this->render('tweets/index.html.twig', [
            'tweets' => $tweetsRepository->findBy([],['createdAt'=>'DESC']),
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

    if(strlen($tweet->getContent())>255) {
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

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('app_tweets_index');
    }

    return $this->render('tweets/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}




//Confirmation de la suppression d'un tweet

#[Route('/profile/tweet/{id}/confirm-delete', name:'app_tweet_confirm_delete', methods:['GET'])]

public function confirmDelete(Tweets $tweet) : Response {

    if ($this->getUser() !== $tweet->getUser()){
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

    if ($this->isCsrfTokenValid('delete'.$tweet->getId(), $request->get('_token'))) {
        $entityManager->remove($tweet);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_profile');
}

}

