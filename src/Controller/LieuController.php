<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuFormType;
use App\Repository\LieuRepository;
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
        EntityManagerInterface $entityManager,
    ): Response
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuFormType::class, $lieu);

        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            return $this->redirectToRoute('lieu_list');
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
                           Request                $request,
                           EntityManagerInterface $entityManager,
    ): Response
    {
        $lieu = $lieuRepository->findLieuById($id);
        $lieuForm = $this->createForm(LieuFormType::class, $lieu);

        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {

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
