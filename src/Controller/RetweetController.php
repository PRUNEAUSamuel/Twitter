<?php

namespace App\Controller;

use App\Entity\Tweets;
use App\Entity\Retweet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\TweetsType;
use Symfony\Component\Form\FormError;


#[Route('/retweet')]

final class RetweetController extends AbstractController
{
    #[Route(name: 'app_retweet')]
    public function index(): Response
    {
        return $this->render('retweet/index.html.twig', [
            'controller_name' => 'RetweetController',
        ]);
    }

    #[Route('/{id}', name: 'app_tweet_retweet', methods: ['GET', 'POST'])]
    public function retweet(Tweets $tweets, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $retweet = new Retweet();
        $retweet->setUser($user);
        $retweet->setTweet($tweets);
        $retweet->setCreatedAt(new \DateTimeImmutable());



        if ($request->isMethod('POST')) {
            $content = $request->request->get('retweet_content');

            if ($content) {
                $retweet->setContent($content);
            }

            $entityManager->persist($retweet);

            $tweets->incrementRetweetCount();
            $entityManager->flush();

            return $this->redirectToRoute('app_tweets_index');
        }

        return $this->render('tweets/retweet.html.twig', [
            'tweet' => $tweets,
        ]);
    }


    //Modifier un retweet
    #[Route('/profile/retweet/{id}/edit', name: 'app_retweet_edit', methods: ['GET', 'POST'])]
    public function editRetweet(Request $request, Retweet $retweet, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est bien le propriétaire du retweet
        if ($this->getUser() !== $retweet->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        $form = $this->createForm(TweetsType::class, $retweet, [
            'data_class' => Retweet::class,  // Spécifiez ici que le formulaire est lié à Retweet
        ]);

        $form->handleRequest($request);

        if (strlen($retweet->getContent()) > 255) {
            $form->get('content')->addError(new FormError("Le contenu du tweet ne peut pas dépasser 255 caractères."));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_tweets_index');
        }

        return $this->render('retweet/edit.html.twig', [
            'form' => $form->createView(),
            'retweet' => $retweet,
        ]);
    }



    // Confirmation suppression Retweet
    #[Route('/profile/retweet/{id}/confirm-delete', name: 'app_retweet_confirm_delete', methods: ['GET', 'POST'])]

    public function confirmDelete(Retweet $retweet): Response
    {

        if ($this->getUser() !== $retweet->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('retweet/confirm_delete.html.twig', ['retweet' => $retweet]);
    }


    
    // Supprimer un retweet 
    #[Route('/profile/retweet/{id}/delete', name: 'app_retweet_delete', methods: ['GET', 'POST'])]
public function deleteRetweet(Request $request, Retweet $retweet, EntityManagerInterface $entityManager): Response
{
    // Vérifier que l'utilisateur est bien le propriétaire du retweet
    if ($this->getUser() !== $retweet->getUser()) {
        return $this->redirectToRoute('app_profile');
    }

    // Validation du token CSRF
    if ($this->isCsrfTokenValid('delete' . $retweet->getId(), $request->get('_token'))) {
        // Récupérer le tweet associé au retweet
        $tweet = $retweet->getTweet();

        // Décrémenter le compteur de retweets du tweet original
        if ($tweet !== null) {
        $tweet->decrementRetweetCount();
        }

        // Supprimer le retweet
        $entityManager->remove($retweet);

        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_profile');
}
}

