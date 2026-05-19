<?php

namespace App\Controller;

use App\Repository\LieuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
