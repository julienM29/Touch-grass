<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Utils\ImageLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie')]
final class SortieController extends AbstractController
{

    #[Route('/', name: '', methods: ['GET'])]
    public function list(
        EntityManagerInterface $em,
    ): Response
    {
        $sorties = $em->getRepository(Sortie::class)->findAll();
        return $this->render('sortie/list.html.twig',[
            'sorties' => $sorties,
        ]);
    }

    #[Route('/create', name: '_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ImageLoader $imageLoader
    ): Response
    {
        $organisateur = $this->getUser();
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $imageFile = $sortieForm->get('image')->getData();

            if ($filename = $imageLoader->uploadImage($imageFile)) {
                $sortie->setImage($filename);
            }
            $sortie->setOrganisateur($organisateur);

            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm,
        ]);
    }

    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(
        int $id,
        EntityManagerInterface $em,
    ): Response
    {
        $sortie = $em->getRepository(Sortie::class)->find($id);
        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/edit', name: '_edit', methods: ['GET', 'POST'])]
    public function edit(Sortie $sortie, Request $request, EntityManagerInterface $em): Response
    {
        $sortie = $em->getRepository(Sortie::class)->find($sortie->getId());
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($sortie);
            $em->flush();

            return $this->redirectToRoute('sortie_show', [
                'id' => $sortie->getId(),
            ]);
        }
        return $this->render('sortie/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: '_delete', methods: ['DELETE'])]
    public function delete(Sortie $sortie, EntityManagerInterface $em): Response
    {
        $em->remove($sortie);
        $em->flush();
        return $this->redirectToRoute('sortie_list');
    }


}
