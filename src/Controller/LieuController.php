<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuFormType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

            // Cas 1 : aucune ville existante choisie -> créer une nouvelle ville
            if ($lieu->getVille() === null) {
                $villeData = $lieuForm->get('nouvelleVille')->getData();

                // Vérif que des données ont bien été saisies
                if ($villeData === null || $villeData->getNom() === null) {
                    // Aucune ville sélectionnée ET aucune nouvelle ville saisie
                    $this->addFlash('error', 'Veuillez choisir ou créer une ville.');
                    return $this->render('lieu/create.html.twig', [
                        'lieuForm' => $lieuForm,
                    ]);
                }

                // Vérif si doublon de ville
                $villeExistante = $villeRepository->findOneBy([
                    'nom' => $villeData->getNom(),
                    'codePostal' => $villeData->getCodePostal(),
                ]);

                if ($villeExistante) {
                    $lieu->setVille($villeExistante);
                } else {
                    $entityManager->persist($villeData);
                    $lieu->setVille($villeData);
                }
            }

            // Vérif si boublon de lieu
            $lieuExistant = $lieuRepository->findOneBy([
                'rue' => $lieu->getRue(),
                'ville' => $lieu->getVille(),
            ]);

            if ($lieuExistant) {
                $this->addFlash('warning', 'Ce lieu existe déjà en base de donnée.');
                return $this->render('lieu/create.html.twig', [
                    'lieuForm' => $lieuForm,
                ]);
            }

            // Cas 2 : ville existante sélectionnée -> on persist + flush
            try {
                $entityManager->persist($lieu);
                $entityManager->flush();
                $this->addFlash('success', 'Le lieu a bien été créé.');
                return $this->redirectToRoute('lieu_list');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement.');
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
    public function update(int                    $id,
                           LieuRepository         $lieuRepository,
                           VilleRepository        $villeRepository,
                           Request                $request,
                           EntityManagerInterface $entityManager,
    ): Response
    {
        $lieu = $lieuRepository->findLieuById($id);
        $lieuForm = $this->createForm(LieuFormType::class, $lieu);

        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $lieu = $lieuForm->getData();

            // Si aucune ville existante choisie -> créer une nouvelle ville
            if ($lieu->getVille() === null) {
                $villeData = $lieuForm->get('nouvelleVille')->getData();

                // Vérif si doublon
                $villeExistante = $villeRepository->findOneBy([
                    'nom' => $villeData->getNom(),
                    'codePostal' => $villeData->getCodePostal(),
                ]);

                if ($villeExistante) {
                    $lieu->setVille($villeExistante);
                } else {
                    $entityManager->persist($villeData);
                    $lieu->setVille($villeData);
                }
            }

            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success', $lieu->getNom() . ' updated !');

            return $this->redirectToRoute('lieu_detail', ['id' => $lieu->getId()]);
        }

        return $this->render('lieu/update.html.twig', [
            'lieuForm' => $lieuForm,
        ]);
    }
}
