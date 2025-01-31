<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Tweets;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/comment')]
final class CommentController extends AbstractController
{
    #[Route(name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    #[Route('/new/{tweetId}/{parentCommentId?}', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(int $tweetId, ?int $parentCommentId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $tweet = $entityManager->getRepository(Tweets::class)->find($tweetId);
        if (!$tweet) {
            throw $this->createNotFoundException('Tweet not found');
        }

        // Si on répond à un commentaire
        $parentComment = null;
        if ($parentCommentId) {
            $parentComment = $entityManager->getRepository(Comment::class)->find($parentCommentId);
            if (!$parentComment) {
                throw $this->createNotFoundException('Parent comment not found');
            }
        }

        $comment = new Comment();
        $comment->setTweet($tweet);
        $comment->setCreatedAt(new \DateTime());
        if ($parentComment) {
            $comment->setParentComment($parentComment);
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser());
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_tweets_index', ['id' => $tweetId]);
        }

        return $this->render('comment/new.html.twig', [
            'form' => $form->createView(),
            'tweet' => $tweet,
            'parentComment' => $parentComment,
        ]);
    
    }

    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            if ($comment->getParentComment()) {
                return $this->redirectToRoute('app_comment_show', ['id' => $comment->getParentComment()->getId()]);
            }

            return $this->redirectToRoute('app_tweets_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
    }
}
