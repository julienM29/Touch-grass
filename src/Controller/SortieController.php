<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\FilterForm;
use App\Form\SortieType;
use App\Services\InitializerService;
use App\Utils\ImageLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie')]
final class SortieController extends AbstractController
{

    #[Route('/', name: '', methods: ['GET'])]
    public function list(
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $filterForm = $this->createForm(FilterForm::class);

        $siteId = $request->query->get('site');

        if ($siteId === null && $this->getUser()?->getSite() !== null) {
            $siteId = $this->getUser()->getSite()->getId();
        }

        $sites = $em->getRepository(\App\Entity\Site::class)->findBy([],
        ['nom' => 'ASC']);

        if ($siteId) {
            $sorties = $em->getRepository(Sortie::class)->findBy([
                'siteOrganisateur' => $siteId,
            ]);
        } else {
            $sorties = $em->getRepository(Sortie::class)->findAll();
        }

        return $this->render('sortie/list.html.twig',[
            'sorties' => $sorties,
            'sites' => $sites,
            'selectedSiteId' => $siteId,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    #[Route('/create', name: '_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ImageLoader $imageLoader
    ): Response
    {
        $organisateur = $this->getUser();

        if (!$organisateur instanceof Participant) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer une event_registration.');
        }

        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $imageFile = $sortieForm->get('image')->getData();

            if ($filename = $imageLoader->replaceImage($imageFile, $sortie->getImage())) {
                $sortie->setImage($filename);
            }
            $sortie->setOrganisateur($organisateur);

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm,
        ]);
    }

    #[Route('/reset', name: '_reset', methods: ['POST'])]
    public function resetData(
        Request $request,
        InitializerService $initializerService,
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('reset_data', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $initializerService->resetAllData();

        $request->getSession()->invalidate();

        return $this->redirectToRoute('app_home');
    }

    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(
        int $id,
        EntityManagerInterface $em,
    ): Response
    {
        $sortie = $em->getRepository(Sortie::class)->find($id);
        $participants = [];
        foreach ($sortie->getParticipants() as $participant) {
            $participants[] = $participant;
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'participants' => $participants,
        ]);
    }


    #[Route('/{id}/edit', name: '_edit', methods: ['GET', 'POST'])]
    public function edit(Sortie $sortie, Request $request, EntityManagerInterface $em, ImageLoader $imageLoader): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($filename = $imageLoader->replaceImage($imageFile, $sortie->getImage())) {
                $sortie->setImage($filename);
            }

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('sortie_show', [
                'id' => $sortie->getId(),
            ]);
        }
        return $this->render('sortie/edit.html.twig', [
            'sortieForm' => $form,
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($sortie->getOrganisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette event_registration.');
        }

        if (!$this->isCsrfTokenValid('delete_sortie_' . $sortie->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $em->remove($sortie);
        $em->flush();

        return $this->redirectToRoute('sortie');
    }
}
