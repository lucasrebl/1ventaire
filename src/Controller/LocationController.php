<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/location')]
#[IsGranted('ROLE_USER')]
class LocationController extends AbstractController
{
    #[Route('/', name: 'app_location_index', methods: ['GET'])]
    public function index(LocationRepository $locationRepository, #[CurrentUser] $user): Response
    {
        return $this->render('location/index.html.twig', [
            'locations' => $locationRepository->findByUser($user),
        ]);
    }

    #[Route('/new', name: 'app_location_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer un inventaire de l'utilisateur actuel
        $inventory = $entityManager->getRepository(\App\Entity\Inventory::class)->findOneBy(['owner' => $this->getUser()]);
        
        if (!$inventory) {
            $this->addFlash('error', 'Vous devez créer un inventaire avant de pouvoir ajouter un emplacement.');
            return $this->redirectToRoute('app_inventory_new');
        }
        
        $location = new Location();
        $location->setInventory($inventory);
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($location);
            $entityManager->flush();

            $this->addFlash('success', 'L\'emplacement a été créé avec succès.');
            return $this->redirectToRoute('app_location_index');
        }

        return $this->render('location/new.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_location_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Location $location, EntityManagerInterface $entityManager): Response
    {
        if ($location->getInventory()->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cet emplacement.');
        }

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'emplacement a été modifié avec succès.');
            return $this->redirectToRoute('app_location_index');
        }

        return $this->render('location/edit.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_location_delete', methods: ['POST'])]
    public function delete(Request $request, Location $location, EntityManagerInterface $entityManager): Response
    {
        if ($location->getInventory()->getOwner() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cet emplacement.');
            return $this->redirectToRoute('app_location_index');
        }

        if (!$this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_location_index');
        }
            
        // Vérifier si l'emplacement est utilisé par des articles
        $items = $location->getItems();
        if (count($items) > 0) {
            $this->addFlash('error', 'Cet emplacement ne peut pas être supprimé car il est utilisé par des articles.');
            return $this->redirectToRoute('app_location_index');
        }
        
        try {
            $entityManager->remove($location);
            $entityManager->flush();
            $this->addFlash('success', 'L\'emplacement a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'emplacement.');
        }

        return $this->redirectToRoute('app_location_index');
    }
}