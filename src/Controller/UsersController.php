<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use App\Entity\Tweets;
use App\Form\TweetsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


#[Route('/users')]
final class UsersController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function profile(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, rediriger vers la page de login
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les tweets de l'utilisateur
        $tweets = $entityManager
            ->getRepository(Tweets::class)
            ->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('users/profile.html.twig', [
            'user' => $user,
            'tweets' => $tweets,
        ]);
    }

    // Modifier les informations du profil de l'utilisateur
    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
       

        
        // Créer le formulaire d'édition
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $newPassword = $form->get('password')->getData();
           if (!$user instanceof Users) {
            throw new \Exception('L\'utilisateur récupéré n\'est pas valide');
            }
         
            if($newPassword){
            
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            }
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis a jour avec succès.');
            return $this->redirectToRoute('app_profile');
        
        }
            return $this->render('users/edit_profile.html.twig', [
            'form' => $form->createView(),
            ]);
         
    }

//     #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
//     public function delete(Request $request, Users $user, EntityManagerInterface $entityManager): Response

//   {

//   }

    // Supprimer un tweet
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

    // Ajouter un tweet
    #[Route('/profile/tweet/new', name: 'app_tweet_new', methods: ['GET', 'POST'])]
    public function newTweet(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tweet = new Tweets();
        $tweet->setUser($this->getUser()); // Lier le tweet à l'utilisateur connecté

        $form = $this->createForm(TweetsType::class, $tweet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tweet);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile');
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
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('tweets/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
  
}





