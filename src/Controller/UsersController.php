<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\TweetsRepository;
use App\Form\UsersType;
use App\Form\SearchType;
use App\Repository\UsersRepository;
use App\Entity\Tweets;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


#[Route('/users')]
final class UsersController extends AbstractController
{
    #[Route('/recherche', name: 'app_search', methods: ['GET', 'POST'])]
     
    public function search(Request $request, UsersRepository $userRepository): response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        $users = [];
        $query = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->get('query')->getData();
            $users = $userRepository->findBySearchQuery($query);

            return $this->redirectToRoute('app_search', ['query' => $query]);
        }

        if ($request->query->get('query')) {
            $query = $request->query->get('query');
            $users = $userRepository->findBySearchQuery($query);
        }

        return $this->render('users/search.html.twig', [
            'form' => $form->createView(),
            'users' => $users,
            'query' => $query
        ]);
    }

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

        /** @var \App\Entity\Users $user */
        $user = $this->getUser();


        if (!$user instanceof Users) {
            throw new \LogicException('The logged-in user is not valid.');
        }


        if (!$user) {
            return $this->redirectToRoute('app_login');
        }


        // Créer le formulaire d'édition
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('username')->getData();
            $email = $form->get('email')->getData();
            $oldPassword = $form->get('oldPassword')->getData();
            $password = $form->get('password')->getData();
            $confirmPassword = $form->get('Confirm_password')->getData();


            // Vérifier si le champ "Nouveau mot de passe" est rempli sans "Ancien mot de passe"
        if ($password && !$oldPassword) {
            $this->addFlash('error', 'Veuillez renseigner votre ancien mot de passe.');
            return $this->redirectToRoute('app_profile_edit');
        }

        // Si "Ancien mot de passe" est rempli mais "Nouveau mot de passe" est vide
        if ($oldPassword && !$password) {
            $this->addFlash('error', 'Veuillez renseigner un nouveau mot de passe.');
            return $this->redirectToRoute('app_profile_edit');
        }

        // Vérifier si "Confirm password" est rempli sans les autres champs de mot de passe
        if ($confirmPassword && (!$password || !$oldPassword)) {
            $this->addFlash('error', 'Si vous voulez confirmer un mot de passe, les deux autres champs doivent être renseignés.');
            return $this->redirectToRoute('app_profile_edit');
        }

        // Vérifier que les mots de passe correspondent
        if ($password && $confirmPassword && $password !== $confirmPassword) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('app_profile_edit');
        }

        // Vérifier l'ancien mot de passe
        if ($oldPassword && $password && !$passwordHasher->isPasswordValid($user, $oldPassword)) {
            $this->addFlash('error', 'Ancien mot de passe incorrect.');
            return $this->redirectToRoute('app_profile_edit');
        }

        // Si les mots de passe sont valides, les hacher
        if ($password && $confirmPassword && $password === $confirmPassword) {
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
        }
            
            if ($username) {
                $user->setUsername($username);
            }
            if ($email) {
                $user->setEmail($email);
            }

            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('profilePicture')->getData();

            if ($photoFile) {
                // Générer un nom de fichier unique basé sur uniqid()
                $newFilename = uniqid() . '.' . $photoFile->guessExtension();

                // Déplacer le fichier vers le dossier public/uploads/profile_pictures
                try {
                    $photoFile->move(
                        $this->getParameter('profile_pictures_directory'), // Dossier où l'image sera sauvegardée
                        $newFilename
                    );
                    $user->setProfilePicture($newFilename); // Enregistrer le nom du fichier dans la base de données
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Impossible de télécharger la photo de profil.');
                    return $this->redirectToRoute('app_profile_edit');
                }
            }

            //  dump($photoFile);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour.');
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('users/edit_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/profile/{id}', name: 'app_user_profile', methods: ['GET'])]
    public function userProfile(int $id, UsersRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Trouver l'utilisateur par son ID
        $user = $userRepository->find($id);

        // Si l'utilisateur n'existe pas, rediriger vers la page d'accueil ou une autre page d'erreur
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_tweets_index');
        }

        // Récupérer les tweets de l'utilisateur
        $tweets = $entityManager
            ->getRepository(Tweets::class)
            ->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('users/profile_search.html.twig', [
            'user' => $user,
            'tweets' => $tweets,
        ]);
    }




    


    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, SessionInterface $session): Response

    {
        /** @var \App\Entity\Users $user */
        $user = $this->getUser();


        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $csrfToken = $request->request->get('_token');

        // Vérifier que le token CSRF est valide
        if (!$this->isCsrfTokenValid('delete' . $user->getId(), $csrfToken)) {
            $this->addFlash('error', 'Token de suppression invalide.');
            return $this->redirectToRoute('app_profile');
        }

        if ($request->isMethod('POST') && $user) {
            // Supprimer l'utilisateur de la base de données
            $entityManager->remove($user);
            $entityManager->flush();

            $tokenStorage->setToken(null);  // Déconnecte l'utilisateur
            $session->invalidate();

            $this->addFlash('success', 'Votre profil a été supprimé avec succès.');

            return $this->redirectToRoute('app_login');
        }
        $this->addFlash('error', 'Erreur lors de la suppression du profil.');
        return $this->redirectToRoute('app_profile');
    }
}
