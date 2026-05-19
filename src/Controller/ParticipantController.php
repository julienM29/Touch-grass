<?php

namespace App\Controller;

use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ParticipantController extends AbstractController
{
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
        return $this->render('participant/index.html.twig', [
            'user' => $user,
            'eventsCreated' => $eventsCreated,
            'participations' => $participations,
            'upcomingEvents' => $upcomingEvents,
            'userEvents' => $userEvents,
        ]);
    }
}
