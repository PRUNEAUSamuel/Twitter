<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
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

            if ($oldPassword) {
                // Vérifier si l'ancien mot de passe est correct
                if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                    $this->addFlash('error', 'Ancien mot de passe incorrect.');
                    return $this->redirectToRoute('app_profile_edit');
                }
                if ($password) {
                    // Vérifier si le champ Confirm_password est rempli
                    if (empty($confirmPassword)) {
                        // Si le champ Confirm_password est vide, afficher une erreur
                        $this->addFlash('error', 'Veuillez confirmer votre mot de passe.');
                        return $this->redirectToRoute('app_profile_edit');
                    }
                }

                if ($password && $confirmPassword) {
                    if ($password !== $confirmPassword) {
                        $this->addFlash('error', 'les mots de passe ne corresponde pas.');
                        return $this->redirectToRoute('app_profile_edit');
                    }

                    $hashedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }
            }
            if ($username) {
                $user->setUsername($username);
            }
            if ($email) {
                $user->setEmail($email);
            }

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
