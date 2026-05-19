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
    public function create(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $ville = new Ville();
        $villeForm = $this->createForm(VilleFormType::class, $ville);

        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

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

    #[Route('/{id}/delete', name: 'delete', methods: ['GET'])]
    public function delete(int                    $id,
                           VilleRepository        $villeRepository,
                           EntityManagerInterface $entityManager): Response
    {
        $ville = $villeRepository->findVilleById($id);

        if ($ville) {
            $entityManager->remove($ville);
            $entityManager->flush();
            $this->addFlash('success', $ville->getNom() . ' deleted !');
        }

        return $this->redirectToRoute('ville_list');
    }
}
