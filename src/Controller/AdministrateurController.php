<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilFormType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Services\ParticipantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'admin_')]
final class AdministrateurController extends AbstractController
{
    public function __construct(
        private ParticipantService $participantService
    )
    {
    }
    #[Route('/', name: 'dashboard')]
    public function index(
        ParticipantRepository $participantRepository,
        SortieRepository $sortieRepository
    ): Response {
        $nbSorties = $sortieRepository->count([]);
        $nbUtilisateurs = $participantRepository->count([]);
        $sortiesAVenir = $sortieRepository->countFuturSorties();
        $sortiesPassees = $sortieRepository->countPastSorties();
        $sortiesAnnulees = $sortieRepository->countCancelledSorties();
        $dernieresSorties = $sortieRepository->findLastSorties();
        $derniersUtilisateurs = $participantRepository->findLastParticipants();
        return $this->render('admin/dashboard.html.twig', [
            'nbSorties' => $nbSorties,
            'nbUtilisateurs' => $nbUtilisateurs,
            'sortiesAVenir' => $sortiesAVenir,
            'sortiesPassees' => $sortiesPassees,
            'sortiesAnnulees' => $sortiesAnnulees,
            'dernieresSorties' => $dernieresSorties,
            'dernieresUtilisateurs' => $derniersUtilisateurs
        ]);
    }
    #[Route('/participant', name: 'participant')]
    public function afficherParticipants(ParticipantRepository $participantRepository ): Response {
        $utilisateurs = $participantRepository->findAll();
        return $this->render('admin/participant/list.html.twig',[
        'utilisateurs' => $utilisateurs]);
    }
    #[Route('/participant/{id}/edit', name: 'participant_edit')]
    public function edit(
        Participant $utilisateur,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $form = $this->createForm(ProfilFormType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $filename = $this->participantService->uploadImage($imageFile);
                $utilisateur->setImage($filename);
            }

            $em->flush();

            return $this->redirectToRoute('admin_participant');
        }
        dump($form);
        return $this->render('admin/participant/edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur
        ]);
    }
    #[Route('/sorties', name: 'sorties')]
    public function afficherSorties(
        Request $request,
        SortieRepository $sortieRepository
    ): Response {

        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $filters = [
            'nom' => $request->query->get('nom'),
            'ville' => $request->query->get('ville'),
            'lieu' => $request->query->get('lieu'),
            'status' => $request->query->get('status'),
            'date' => $request->query->get('date'),
        ];

        $sorties = $sortieRepository->findFilteredPaginated($filters, $page, $limit);

        $hasMore = count($sorties) > $limit;

        if ($hasMore) {
            array_pop($sorties);
        }

        return $this->render('admin/sorties/list.html.twig', [
            'sorties' => $sorties,
            'page' => $page,
            'hasMore' => $hasMore,
        ]);
    }
    #[Route('/site', name: 'site')]
    public function afficherSite(  ): Response {
        return $this->render('admin/site/list.html.twig');
    }
    #[Route('/ville', name: 'ville')]
    public function afficherVille(  ): Response {
        return $this->render('admin/ville/list.html.twig');
    }
}
