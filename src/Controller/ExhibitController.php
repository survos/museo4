<?php

namespace App\Controller;

use App\Entity\Exhibit;
use App\Form\ExhibitType;
use App\Repository\ExhibitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class ExhibitController extends AbstractController
{
    /**
     * @Route("/player/{id}", name="exhibit_player", methods={"GET"})
     */
    public function player(Exhibit $exhibit): Response
    {
        return $this->render('exhibit/player.html.twig', [
            'exhibit' => $exhibit
        ]);
    }

    /**
     * @Route("/", name="exhibit_index", methods={"GET"})
     */
    public function index(ExhibitRepository $exhibitRepository): Response
    {
        return $this->render('exhibit/index.html.twig', [
            'exhibits' => $exhibitRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="exhibit_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $exhibit = new Exhibit();
        $form = $this->createForm(ExhibitType::class, $exhibit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($exhibit);
            $entityManager->flush();

            return $this->redirectToRoute('exhibit_index');
        }

        return $this->render('exhibit/new.html.twig', [
            'exhibit' => $exhibit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="exhibit_show", methods={"GET"})
     */
    public function show(Exhibit $exhibit): Response
    {
        return $this->render('exhibit/show.html.twig', [
            'exhibit' => $exhibit,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="exhibit_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Exhibit $exhibit): Response
    {
        $form = $this->createForm(ExhibitType::class, $exhibit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('exhibit_index', [
                'id' => $exhibit->getId(),
            ]);
        }

        return $this->render('exhibit/edit.html.twig', [
            'exhibit' => $exhibit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="exhibit_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Exhibit $exhibit): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exhibit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($exhibit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('exhibit_index');
    }
}
