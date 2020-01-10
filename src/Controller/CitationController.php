<?php

namespace App\Controller;

use App\Entity\Citation;
use App\Entity\Author;
use App\Form\CitationType;
use App\Repository\CitationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @Route("/")
 */
class CitationController extends AbstractController
{
    /**
     * @Route("/", name="citation_index", methods={"GET"})
     */
    public function index(CitationRepository $citationRepository, Request $request): Response
    {
        $locale = $request->getPreferredLanguage($this->getParameter('locales'));
        if ($locale && $locale != $request->getLocale()) {
            return $this->redirectToRoute('citation_index', ['_locale' => $locale]);
        }
        return $this->render('citation/index.html.twig', [
            'citation' => $citationRepository->getOneRandomCitation(),
        ]);
    }

    /**
     * @Route("/admin/new", name="citation_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $citation = new Citation();
        $form = $this->createForm(CitationType::class, $citation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new = $form->get('new_author')->getData();
            $entityManager = $this->getDoctrine()->getManager();
            if ($new) {
                $author = new Author();
                $author->setFullname($new);
                $entityManager->persist($author);
                $entityManager->flush();
                $citation->setAuthor($author);
            }
            $entityManager->persist($citation);
            $entityManager->flush();
            $this->addFlash('success', 'Note created');
            return $this->redirectToRoute('citation_show', ['id' => $citation->getId()]);
        }

        return $this->render('citation/new.html.twig', [
            'citation' => $citation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="citation_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Citation $citation): Response
    {
        return $this->render('citation/index.html.twig', [
            'citation' => $citation,
        ]);
    }

    /**
     * @Route("/admin/{id}/edit", name="citation_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function edit(Request $request, Citation $citation): Response
    {
        $form = $this->createForm(CitationType::class, $citation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            if ($new = $form->get('new_author')->getData()) {
                $author = new Author();
                $author->setFullname($new);
                $entityManager->persist($author);
                $entityManager->flush();
                $citation->setAuthor($author);
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Change Saved');
            return $this->redirectToRoute('citation_show', ['id' => $citation->getId()]);
        }

        return $this->render('citation/edit.html.twig', [
            'citation' => $citation,
            'author' => $citation->getAuthor(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/{id}", name="citation_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete(Request $request, Citation $citation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$citation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($citation);
            $entityManager->flush();
            $this->addFlash('success', 'Note deleted'); 
        }

        return $this->redirectToRoute('citation_index');
    }


    /**
     * @Route("/admin/{id}/duplicate", name="citation_duplicate", methods={"POST"}, requirements={"id":"\d+"})
     */
    public function duplicate(Request $request, Citation $citation): Response
    {
        if ($this->isCsrfTokenValid('duplicate'.$citation->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $new =  clone $citation;
            $em->persist($new);
            $em->flush();
            $this->addFlash('success', 'Note duplicated'); 
            return $this->redirectToRoute('citation_edit', ['id' => $new->getId()]);
        }
        return $this->redirectToRoute('citation_index');
    }

    /**
     * @Route("/search", name="search", methods={"GET"})
    */
    public function search(Request $request, CitationRepository $citationRepository): Response
    {
        $searchform = $this->createSearchForm();
        $searchform->handleRequest($request);
        if ($searchform->isSubmitted() && $searchform->isValid()) {
            $data = $searchform->getData();
            if (mb_strlen($data['text']) < 3) {
                $title = 'Search too short';
                $citations = array();
            } else {
                $title = 'Search result';
                $citations = $citationRepository->search($data['text']);
            }
        }

        return $this->render('citation/list.html.twig', [
            'citations' => $citations,
            'titre' => $title,
            'search' => $data['text']
        ]);
    }

    public function showSearch()
    {
        return $this->render('citation/_search.html.twig', [
            'form' => $this->createSearchForm()->createView()
        ]);
    }

    protected function createSearchForm() {
        return $this->createFormBuilder(null)
            ->add('text', TextType::class)
            ->setMethod('GET')
            ->setAction($this->generateUrl('search'))
            ->getForm()
            ;
    }
}
