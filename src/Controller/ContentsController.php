<?php

namespace App\Controller;

use App\Entity\Contents;
use App\Form\ContentsType;
use App\Repository\ContentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contents')]
final class ContentsController extends AbstractController{
    #[Route(name: 'app_contents_index', methods: ['GET'])]
    public function index(ContentsRepository $contentsRepository): Response
    {
        return $this->render('contents/index.html.twig', [
            'contents' => $contentsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contents_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $content = new Contents();
        $form = $this->createForm(ContentsType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($content);
            $entityManager->flush();

            return $this->redirectToRoute('app_contents_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contents/new.html.twig', [
            'content' => $content,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contents_show', methods: ['GET'])]
    public function show(Contents $content): Response
    {
        return $this->render('contents/show.html.twig', [
            'content' => $content,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contents_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contents $content, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContentsType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_contents_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contents/edit.html.twig', [
            'content' => $content,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contents_delete', methods: ['POST'])]
    public function delete(Request $request, Contents $content, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$content->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($content);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contents_index', [], Response::HTTP_SEE_OTHER);
    }
}
