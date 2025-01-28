<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\TweetsRepository;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use App\Entity\Tweets;
use App\Form\TweetsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


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

    // #[Route('/profile/{username}', name: 'app_user_profile', methods: ['GET'])]
    // public function profile(Users $user, TweetsRepository $tweetsRepository) {
    //     $tweets = $tweetsRepository->findByUser($user);

    //     return $this->render("users/profile.html.twig", [
    //         'user' => $user,
    //         'tweets' => $tweets
    //     ]);
    // }

    // Modifier les informations du profil de l'utilisateur
    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
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
            $entityManager->flush();
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

}