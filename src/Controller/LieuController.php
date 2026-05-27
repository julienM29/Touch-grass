<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuFormType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/lieu', name: 'lieu_')]
final class LieuController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(LieuRepository $lieuRepository): Response
    {

        $lieux = $lieuRepository->findAllLieu();

        return $this->render('lieu/list.html.twig', [
            'lieux' => $lieux,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        Request                $request,
        VilleRepository        $villeRepository,
        LieuRepository         $lieuRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuFormType::class, $lieu);

        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $lieu = $lieuForm->getData();

            $villeNom = $lieuForm->get('villeNom')->getData();
            $villeCP  = $lieuForm->get('villeCodePostal')->getData();

            // Vérif doublon lieu AVANT de créer la ville
            $lieuExistant = $lieuRepository->findOneBy([
                'rue'  => $lieu->getRue(),
                'nom'  => $lieu->getNom(),
            ]);

            if ($lieuExistant) {
                $this->addFlash('warning', 'Ce lieu existe déjà.');
                return $this->render('lieu/create.html.twig', ['lieuForm' => $lieuForm]);
            }

            // Vérif doublon ville
            $ville = $villeRepository->findOneBy([
                'nom'        => $villeNom,
                'codePostal' => $villeCP,
            ]);

            // Créer la ville seulement si elle n'existe pas
            if (!$ville) {
                $ville = new Ville();
                $ville->setNom($villeNom);
                $ville->setCodePostal($villeCP);
                $entityManager->persist($ville);
            }

            $lieu->setVille($ville);

            try {
                $entityManager->persist($lieu);
                $entityManager->flush();
                $this->addFlash('success', 'Lieu créé avec succès.');
                return $this->redirectToRoute('lieu_list');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue.');
            }
        }

        return $this->render('lieu/create.html.twig', [
            'lieuForm' => $lieuForm,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id, LieuRepository $lieuRepository): Response
    {
        $lieu = $lieuRepository->findLieuById($id);

        if (!$lieu) {
            throw $this->createNotFoundException('Ooops ! Lieu not found !');
        }
        return $this->render('lieu/detail.html.twig', [
            'lieu' => $lieu,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int                    $id,
                           LieuRepository         $lieuRepository,
                           EntityManagerInterface $entityManager): Response
    {
        $lieu = $lieuRepository->findLieuById($id);

        if ($lieu) {
            $entityManager->remove($lieu);
            $entityManager->flush();
            $this->addFlash('success', $lieu->getNom() . ' deleted !');
        }

        return $this->redirectToRoute('lieu_list');
    }

    #[Route('/{id}/update', name: 'update', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(
        int                    $id,
        LieuRepository         $lieuRepository,
        VilleRepository        $villeRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $lieu = $lieuRepository->findLieuById($id);

        if (!$lieu) {
            throw $this->createNotFoundException('Lieu introuvable.');
        }

        // Préremplir les champs ville non mappés
        $lieuForm = $this->createForm(LieuFormType::class, $lieu, [
            'ville_nom'        => $lieu->getVille()?->getNom(),
            'ville_code_postal' => $lieu->getVille()?->getCodePostal(),
        ]);

        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $villeNom = $lieuForm->get('villeNom')->getData();
            $villeCP  = $lieuForm->get('villeCodePostal')->getData();

            // Vérif doublon lieu en excluant le lieu actuel
            $lieuExistant = $lieuRepository->findOneBy([
                'rue' => $lieu->getRue(),
                'nom' => $lieu->getNom(),
            ]);

            if ($lieuExistant && $lieuExistant->getId() !== $lieu->getId()) {
                $this->addFlash('warning', 'Un lieu avec ce nom et cette rue existe déjà.');
                return $this->render('lieu/update.html.twig', [
                    'lieuForm' => $lieuForm,
                    'lieu'     => $lieu,
                ]);
            }

            // Vérif doublon ville
            $ville = $villeRepository->findOneBy([
                'nom'        => $villeNom,
                'codePostal' => $villeCP,
            ]);

            if (!$ville) {
                $ville = new Ville();
                $ville->setNom($villeNom);
                $ville->setCodePostal($villeCP);
                $entityManager->persist($ville);
            }

            $lieu->setVille($ville);

            try {
                $entityManager->flush();
                $this->addFlash('success', $lieu->getNom() . ' mis à jour avec succès.');
                return $this->redirectToRoute('lieu_detail', ['id' => $lieu->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour.');
            }
        }

        return $this->render('lieu/update.html.twig', [
            'lieuForm' => $lieuForm,
            'lieu' => $lieu,
        ]);
    }

    #[Route('/api/create', name: 'api_create', methods: ['POST'])]
    public function apiCreate(
        Request                $request,
        VilleRepository        $villeRepository,
        LieuRepository         $lieuRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $data = json_decode($request->getContent(), true);

        $nom      = $data['nom'] ?? null;
        $rue      = $data['rue'] ?? null;
        $villeNom = $data['villeNom'] ?? null;
        $villeCP  = $data['villeCodePostal'] ?? null;
        $lat      = $data['latitude'] ?? null;
        $lng      = $data['longitude'] ?? null;

        if (!$nom || !$rue || !$villeNom || !$villeCP) {
            return $this->json(['error' => 'Champs manquants.'], 400);
        }

        // Vérif doublon lieu
        $lieuExistant = $lieuRepository->findOneBy(['rue' => $rue, 'nom' => $nom]);
        if ($lieuExistant) {
            return $this->json([
                'error'  => 'Ce lieu existe déjà.',
                'lieuId' => $lieuExistant->getId(),
                'lieuNom' => $lieuExistant->getNom(),
            ], 409);
        }

        // Vérif doublon ville
        $ville = $villeRepository->findOneBy(['nom' => $villeNom, 'codePostal' => $villeCP]);
        if (!$ville) {
            $ville = new Ville();
            $ville->setNom($villeNom);
            $ville->setCodePostal($villeCP);
            $entityManager->persist($ville);
        }

        $lieu = new Lieu();
        $lieu->setNom($nom);
        $lieu->setRue($rue);
        $lieu->setVille($ville);
        if ($lat) $lieu->setLatitude((float) $lat);
        if ($lng) $lieu->setLongitude((float) $lng);

        $entityManager->persist($lieu);
        $entityManager->flush();

        return $this->json([
            'lieuId'  => $lieu->getId(),
            'lieuNom' => $lieu->getNom(),
        ], 201);
    }
}
