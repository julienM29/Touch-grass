<?php

namespace App\Controller;

use App\Dto\FilterDto;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/sortie', name: 'api_sortie_')]
final class SortieRestController extends AbstractController
{
    private SortieRepository $sortieRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->sortieRepository = $entityManager->getRepository(Sortie::class);
    }

    #[Route('-filtered', name: 'list_filtered', methods: ['POST'])]
    public function listFiltered(
        #[MapRequestPayload] FilterDto $filters
    ): JsonResponse {

        $filteredSorties = $this->sortieRepository->findFilteredSorties($filters, $this->getUser()->getId());

        $sortiesData = array_map(static function (Sortie $sortie): array {
            return [
                'id' => $sortie->getId(),
                'nom' => $sortie->getNom(),
                'dateHeureDebut' => $sortie->getDateHeureDebut()?->format('Y-m-d H:i:s'),
                'site' => $sortie->getSiteOrganisateur()?->getNom(),
                'nbParticipantsInscrits' => $sortie->getNbParticipantsInscrits(),
                'nbInscriptionsMax' => $sortie->getNbInscriptionsMax(),
                'complete' => $sortie->isComplete(),
                'inscriptionsOpen' => $sortie->areInscriptionsOpen(),
                'image' => $sortie->getImage(),
            ];
        }, $filteredSorties);

        return new JsonResponse([
            'success' => true,
            'sorties' => $sortiesData,
        ]);
    }

    #[Route('/{id}/register', name: 'register', methods: ['POST'])]
    public function register(
        Request                $request,
        Sortie                 $sortie,
        EntityManagerInterface $em,
    ): JsonResponse
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $participant = $this->getUser();

        if (!$participant instanceof Participant) {
            return $this->sortieRegistrationResponse(
                $sortie,
                'Vous devez être connecté pour vous inscrire.',
                false,
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$this->isCsrfTokenValid('register_sortie_' . $sortie->getId(), $request->request->get('_token'))) {
            return $this->sortieRegistrationResponse(
                $sortie,
                'Token CSRF invalide.',
                false,
                Response::HTTP_FORBIDDEN
            );
        }

        if ($sortie->hasParticipant($participant)) {
            return $this->sortieRegistrationResponse(
                $sortie,
                'Vous êtes déjà inscrit à cette sortie.',
                false,
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!$sortie->areInscriptionsOpen()) {
            return $this->sortieRegistrationResponse(
                $sortie,
                'Les inscriptions ne sont pas ouvertes pour cette sortie.',
                false,
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($sortie->isComplete()) {
            return $this->sortieRegistrationResponse(
                $sortie,
                'Cette sortie est complète.',
                false,
                Response::HTTP_BAD_REQUEST
            );
        }

        $sortie->addParticipant($participant);
        $em->flush();

        return $this->sortieRegistrationResponse(
            $sortie,
            'Vous êtes inscrit à cette sortie.',
            true
        );
    }

    #[Route('/{id}/unregister', name: 'unregister', methods: ['POST'])]
    public function unregister(
        Request                $request,
        Sortie                 $sortie,
        EntityManagerInterface $em,
    ): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $participant = $this->getUser();

        if (!$participant instanceof Participant) {
            return $this->sortieRegistrationResponse(
                $sortie,
                'Vous devez être connecté pour vous désinscrire.',
                false,
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$this->isCsrfTokenValid('unregister_sortie_' . $sortie->getId(), $request->request->get('_token'))) {
            return $this->sortieRegistrationResponse(
                $sortie,
                'Token CSRF invalide.',
                false,
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$sortie->hasParticipant($participant)) {
            return $this->sortieRegistrationResponse(
                $sortie,
                'Vous n’êtes pas inscrit à cette sortie.',
                false,
                Response::HTTP_BAD_REQUEST
            );
        }

        $sortie->removeParticipant($participant);
        $em->flush();

        return $this->sortieRegistrationResponse(
            $sortie,
            'Vous êtes désinscrit de cette sortie.',
            true
        );
    }

    private function sortieRegistrationResponse(
        Sortie $sortie,
        string $message,
        bool   $success,
        int    $status = Response::HTTP_OK,
    ): JsonResponse
    {
        $participant = $this->getUser();

        return new JsonResponse([
            'success' => $success,
            'message' => $message,
            'registered' => $participant instanceof Participant && $sortie->hasParticipant($participant),
            'nbParticipantsInscrits' => $sortie->getNbParticipantsInscrits(),
            'nbInscriptionsMax' => $sortie->getNbInscriptionsMax(),
            'complete' => $sortie->isComplete(),
            'inscriptionsOpen' => $sortie->areInscriptionsOpen(),
        ], $status);
    }
}
