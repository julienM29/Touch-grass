<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\MotDePasseFormType;
use App\Form\ProfilFormType;
use App\Mapper\ProfilMapper;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\TodoRepository;
use App\Services\MotDePasseService;
use App\Services\ParticipantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ParticipantController extends AbstractController
{
    private SortieRepository $sortieRepository;
    private ParticipantService $participantService;
    private ParticipantRepository $participantRepository;

    public function __construct(
        SortieRepository   $sortieRepository,
        ParticipantService $participantService,
        ParticipantRepository $participantRepository
    )
    {
        $this->sortieRepository = $sortieRepository;
        $this->participantService = $participantService;
        $this->participantRepository = $participantRepository;
    }

    #[Route('/participant', name: 'app_participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }

    #[Route('/profil/modify', name: 'participant_profil_modify')]
    #[IsGranted('ROLE_USER')]
    public function modifyProfil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfilFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $filename = $this->participantService->replaceImage($imageFile, $user->getImage());
                if ($filename) {
                    $user->setImage($filename);
                }
            }
            $entityManager->flush();
            return $this->redirectToRoute('participant_profil');
        }

        return $this->render('participant/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profil', name: 'participant_profil')]
    #[Route('/profil/{id}', name: 'participant_profil_id')]
    #[IsGranted('ROLE_USER')]
    public function profil(
        ?int $id = null
    ): Response
    {
        if ($id === null) {
            $user = $this->getUser();
        } else {
            $user = $this->participantRepository->find($id);
        }

        if (!$user instanceof Participant) {
            throw $this->createNotFoundException('Participant introuvable.');
        }

        $eventsCreated = $this->sortieRepository->countByOrganisateur($user->getId());
        $participations = $this->sortieRepository->countPastByParticipant($user->getId());
        $userEvents = $this->sortieRepository->findFuturSortiesByOrganisateur($user->getId());
        $recentEvents = $this->sortieRepository->findRecentSortiesByParticipant($user->getId());
        $upcomingEvents = count($userEvents);

        return $this->render('participant/detail.html.twig', [
            'user' => $user,
            'userEvents' => $userEvents,
            'eventsCreated' => $eventsCreated,
            'participations' => $participations,
            'upcomingEvents' => $upcomingEvents,
            'recentEvents' => $recentEvents,
        ]);
    }

    #[Route('/profil/modify-motdepasse', name: 'participant_password_modify')]
    #[IsGranted('ROLE_USER')]
    public function modifyPassword(Request $request, EntityManagerInterface $entityManager, MotDePasseService $motDePasseService
    ): Response
    {

        $user = $this->getUser();

        if (!$user instanceof Participant) { // permet d'empecher l'erreur du type de user dans changePassword
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(MotDePasseFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $motDePasseActuel = $form->get('current_password')->getData();
            $newMotDePasse = $form->get('new_password')->getData();

            if ($motDePasseService->changePassword($user, $motDePasseActuel, $newMotDePasse, $form)) {

                $entityManager->flush();

                return $this->redirectToRoute('participant_profil');
            }
        }

        return $this->render('password/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/participant/delete', name: 'participant_delete_account')]
    public function deleteAccount(SortieRepository $sortieRepository,ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        // anonymisation email deleted_11 par exemple et en user name deleted_user_11
        $participantRepository->anonymizeUser($user, $entityManager, $sortieRepository);
        $security->logout(false);

        return $this->redirectToRoute('app_login');
    }
}
