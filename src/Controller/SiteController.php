<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteFormType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/site')]
final class SiteController extends AbstractController
{
    #[Route('/create', name: 'site_create', methods: ['GET', 'POST'])]
    public function createSite( Request $request, EntityManagerInterface $entityManager ): Response {
        $site = new Site();
        $form = $this->createForm(SiteFormType::class, $site);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('site/list.html.twig', [
            'siteForm' => $form->createView(),
        ]);
    }
}
