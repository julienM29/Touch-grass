<?php

namespace App\Controller;

use App\Form\ProfilFormType;
use App\Repository\TodoRepository;
use App\Services\ParticipantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ParticipantController extends AbstractController
{

    public function __construct(
        private ParticipantService $participantService
    ) {}

    #[Route('/participant', name: 'app_participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }
    #[Route('/profil', name: 'participant_profil')]
    #[IsGranted('ROLE_USER')]
    public function profil(): Response
    {
        $user = $this->getUser();

        //       $eventsCreated = $eventRepository->countByOrganizer($user);
        //        $participations = $participationRepository->countByUser($user);
        //        $upcomingEvents = $eventRepository->countUpcomingByUser($user);
        //        $userEvents = $eventRepository->findByOrganizer($user);
        $upcomingEvents = 0;
        $userEvents = 0;
        $eventsCreated = 0;
        $participations = 0;
        return $this->render('participant/detail.html.twig', [
            'user' => $user,
            'eventsCreated' => $eventsCreated,
            'participations' => $participations,
            'upcomingEvents' => $upcomingEvents,
            'userEvents' => $userEvents,
        ]);
    }

    #[Route('/profil/modify', name: 'participant_profil_modify')]
    #[IsGranted('ROLE_USER')]
    public function modifyProfil( Request $request,  EntityManagerInterface $entityManager ): Response {
        $user = $this->getUser();

        $form = $this->createForm(ProfilFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 🖼️ image upload
            $imageFile = $form->get('image')->getData();
            if ($filename = $this->participantService->uploadImage($imageFile)) {
                $user->setImage($filename);
            }
            $entityManager->flush();
            return $this->redirectToRoute('participant_profil');
        }

        return $this->render('participant/detail.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
