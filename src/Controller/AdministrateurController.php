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
    #[Route('/participants', name: 'participant')]
    public function afficherParticipants(
        Request $request,
        ParticipantRepository $participantRepository
    ): Response {

        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $filters = [
            'email' => $request->query->get('email'),
            'nom' => $request->query->get('nom'),
            'prenom' => $request->query->get('prenom'),
            'telephone' => $request->query->get('telephone'),
            'actif' => $request->query->get('actif'),
        ];

        $participants = $participantRepository->findFilteredPaginated(
            $filters,
            $page,
            $limit
        );

        $hasMore = count($participants) > $limit;

        if ($hasMore) {
            array_pop($participants);
        }

        return $this->render('admin/participant/list.html.twig', [
            'participants' => $participants,
            'page' => $page,
            'hasMore' => $hasMore,
        ]);
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
        return $this->render('admin/participant/edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur
        ]);
    }
    #[Route('/user/{id}/delete', name: 'user_delete')]
    public function deleteUser(
        Participant $user,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager,
        SortieRepository $sortieRepository
    ): Response {

        $participantRepository->anonymizeUser($user, $entityManager, $sortieRepository);

        return $this->redirectToRoute('admin_participant');
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
        $results = $sortieRepository->findFilteredPaginated($filters, $page, $limit);
        $now = new \DateTime();

        // FILTRAGE MÉTIER
        if (($filters['status'] ?? null) === 'past') {
            // On trie les résultats car on a pas de date fin en bdd donc pas de between etc
            $results = array_filter($results, function ($sortie) use ($now) {
                return $sortie->getDateFin() < $now;
            });

        } elseif (($filters['status'] ?? null) === 'ongoing') {

            $results = array_filter($results, function ($sortie) use ($now) {
                return $sortie->getDateHeureDebut() <= $now
                    && $sortie->getDateFin() >= $now;
            });
        }

        // PAGINATION calcul du nb res et comparaison limit
        $hasMore = count($results) > $limit;
        // Enleve le reste du tab actuelle
        if ($hasMore) {
            array_pop($results);
        }

        return $this->render('admin/sorties/list.html.twig', [
            'sorties' => $results,
            'page' => $page,
            'hasMore' => $hasMore,
        ]);
    }
    #[Route('/sortie/cancel/{id}', name: 'sortie_cancel', methods: ['POST'])]
    public function annulerSortie( int $id, Request $request, SortieRepository $sortieRepository, EntityManagerInterface $entityManager
    ): Response {
        $sortie = $sortieRepository->find($id);
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie introuvable');
        }
        $sortie->setMotifAnnulation('Événement jugé inapproprié par l\'administrateur');

        $sortie->setDateModification(new \DateTime());

        $entityManager->flush();

        $this->addFlash('success', 'Sortie annulée avec succès');

        return $this->redirectToRoute('admin_sorties');
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
