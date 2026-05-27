<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleFormType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ville', name: 'ville_')]
final class VilleController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(VilleRepository $villeRepository): Response
    {

        $villes = $villeRepository->findAllVille();

        return $this->render('ville/list.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        Request                $request,
        EntityManagerInterface $entityManager,
        VilleRepository        $villeRepository,
    ): Response
    {
        $ville = new Ville();
        $villeForm = $this->createForm(VilleFormType::class, $ville);

        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {

            // Vérif doublon
            $villeExistante = $villeRepository->findOneBy([
                'nom' => $ville->getNom(),
                'codePostal' => $ville->getCodePostal(),
            ]);

            if ($villeExistante) {
                $this->addFlash('warning', 'Cette ville existe déjà.');
                return $this->render('ville/create.html.twig', ['villeForm' => $villeForm]);
            }

            $entityManager->persist($ville);
            $entityManager->flush();
            $this->addFlash('success', $ville->getNom() . ' créée avec succès.');

            return $this->redirectToRoute('ville_list');
        }

        return $this->render('ville/create.html.twig', [
            'villeForm' => $villeForm,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id, VilleRepository $villeRepository): Response
    {
        $ville = $villeRepository->findVilleById($id);

        if (!$ville) {
            throw $this->createNotFoundException('Ooops ! Ville not found !');
        }
        return $this->render('ville/detail.html.twig', [
            'ville' => $ville,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int                    $id,
                           VilleRepository        $villeRepository,
                           EntityManagerInterface $entityManager,
                           Request                $request
    ): Response
    {
        $ville = $villeRepository->findVilleById($id);

        if (!$this->isCsrfTokenValid('delete_ville_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('ville_list');
        }

        if ($ville) {

            // Vérif si la ville a des lieux associés
            if (!$ville->getLieus()->isEmpty()) {
                $this->addFlash('error', 'Impossible de supprimer cette ville, elle a des lieux associés.');
                return $this->redirectToRoute('ville_list');
            }

            $entityManager->remove($ville);
            $entityManager->flush();
            $this->addFlash('success', $ville->getNom() . ' deleted !');
        }

        return $this->redirectToRoute('ville_list');
    }

    #[Route('/{id}/update', name: 'update', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(int                    $id,
                           VilleRepository        $villeRepository,
                           Request                $request,
                           EntityManagerInterface $entityManager,
    ): Response
    {
        $ville = $villeRepository->findVilleById($id);
        $villeForm = $this->createForm(VilleFormType::class, $ville);

        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {

            // Vérif doublon en excluant la ville actuelle
            $villeExistante = $villeRepository->findOneBy([
                'nom' => $ville->getNom(),
                'codePostal' => $ville->getCodePostal(),
            ]);

            if ($villeExistante && $villeExistante->getId() !== $ville->getId()) {
                $this->addFlash('warning', 'Une ville avec ce nom et ce code postal existe déjà.');
                return $this->render('ville/update.html.twig', ['villeForm' => $villeForm]);
            }

            $entityManager->flush();
            $this->addFlash('success', $ville->getNom() . ' updated !');
            return $this->redirectToRoute('ville_detail', ['id' => $ville->getId()]);
        }

        return $this->render('ville/update.html.twig', [
            'villeForm' => $villeForm,
        ]);
    }
}
