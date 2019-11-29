<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use App\Repository\CitationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/author")
 */
class AuthorController extends AbstractController
{
    /**
     * @Route("/", name="author_index", methods={"GET"})
     */
    public function index(AuthorRepository $authorRepository): Response
    {
        return $this->render('author/index.html.twig', [
            'authors' => $authorRepository->findAllWithNotesCount(),
        ]);
    }

    /**
     * @Route("/{id}/list", name="author_notes_list", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function list(Author $author, CitationRepository $citationRepository): Response
    {
        return $this->render('citation/list.html.twig', [
            'citations' => $citationRepository->findAllByAuthor($author),
            'titre' => 'Author Notes',
            'author' => $author
        ]);
    }

    /**
     * @Route("/{id}", name="author_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Author $author): Response
    {
        return $this->render('author/show.html.twig', [
            'author' => $author,
        ]);
    }

    /**
     * @Route("/admin/{id}/edit", name="author_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function edit(Request $request, Author $author): Response
    {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Change Saved'); 
            return $this->redirectToRoute('author_index');
        }

        return $this->render('author/edit.html.twig', [
            'author' => $author,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/{id}", name="author_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete(Request $request, Author $author): Response
    {
        if ($this->isCsrfTokenValid('delete'.$author->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($author);
            $entityManager->flush();
            $this->addFlash('success', 'Author deleted');
        }

        return $this->redirectToRoute('author_index');
    }
}
