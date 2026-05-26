<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'admin_')]
final class AdministrateurController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function index(
        ParticipantRepository $participantRepository,
        SortieRepository $sortieRepository
    ): Response {
        $nbSorties = $sortieRepository->count([]);
        $nbUtilisateurs = $participantRepository->count([]);
        $sortiesAVenir = $sortieRepository->countFuturSorties();
        $sortiesPassees = $sortieRepository->countPastSorties();
        $sortiesAnnulees = $sortieRepository->countCancelledSorties();
        return $this->render('admin/dashboard.html.twig', [
            'nbSorties' => $nbSorties,
            'nbUtilisateurs' => $nbUtilisateurs,
            'sortiesAVenir' => $sortiesAVenir,
            'sortiesPassees' => $sortiesPassees,
            'sortiesAnnulees' => $sortiesAnnulees,
        ]);
    }
}
