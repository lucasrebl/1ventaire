<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Form\InventoryType;
use App\Repository\InventoryRepository;
use App\Repository\SharedInventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/inventory')]
#[IsGranted('ROLE_USER')]
class InventoryController extends AbstractController
{
    #[Route('/', name: 'app_inventory_index', methods: ['GET'])]
    public function index(InventoryRepository $inventoryRepository, SharedInventoryRepository $sharedInventoryRepository): Response
    {
        $user = $this->getUser();
        $sharedWithMe = $sharedInventoryRepository->findSharedWithUser($user);

        return $this->render('inventory/index.html.twig', [
            'inventories' => $inventoryRepository->findBy(['owner' => $user]),
            'shared_inventories' => $sharedWithMe,
        ]);
    }

    #[Route('/new', name: 'app_inventory_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $inventory = new Inventory();
        $inventory->setOwner($this->getUser());
        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inventory);
            $entityManager->flush();

            return $this->redirectToRoute('app_inventory_index');
        }

        return $this->render('inventory/new.html.twig', [
            'inventory' => $inventory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inventory_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'inventaire manuellement pour gérer le cas où il n'existe pas
        $inventory = $entityManager->getRepository(Inventory::class)->find($id);
        
        // Rediriger vers la liste des inventaires si l'inventaire n'existe pas
        if (!$inventory) {
            $this->addFlash('error', 'L\'inventaire demandé n\'existe pas.');
            return $this->redirectToRoute('app_inventory_index');
        }
        
        // Vérifier les droits d'accès sans lancer d'exception
        if (!$this->isGranted('view', $inventory)) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cet inventaire.');
            return $this->redirectToRoute('app_inventory_index');
        }

        // Vérification des permissions pour les actions sur l'inventaire
        $canEdit = $this->isGranted('edit', $inventory);
        $canDelete = $this->isGranted('delete', $inventory);
        $isOwner = ($inventory->getOwner() === $this->getUser());

        return $this->render('inventory/show.html.twig', [
            'inventory' => $inventory,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'is_owner' => $isOwner,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_inventory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'inventaire manuellement pour gérer le cas où il n'existe pas
        $inventory = $entityManager->getRepository(Inventory::class)->find($id);
        
        // Rediriger vers la liste des inventaires si l'inventaire n'existe pas
        if (!$inventory) {
            $this->addFlash('error', 'L\'inventaire demandé n\'existe pas.');
            return $this->redirectToRoute('app_inventory_index');
        }
        
        // Vérifier les droits d'accès sans lancer d'exception
        if (!$this->isGranted('edit', $inventory)) {
            $this->addFlash('error', 'Vous n\'avez pas les droits nécessaires pour modifier cet inventaire.');
            return $this->redirectToRoute('app_inventory_index');
        }
        
        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_inventory_index');
        }

        return $this->render('inventory/edit.html.twig', [
            'inventory' => $inventory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inventory_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'inventaire manuellement pour gérer le cas où il n'existe pas
        $inventory = $entityManager->getRepository(Inventory::class)->find($id);
        
        // Rediriger vers la liste des inventaires si l'inventaire n'existe pas
        if (!$inventory) {
            $this->addFlash('error', 'L\'inventaire demandé n\'existe pas.');
            return $this->redirectToRoute('app_inventory_index');
        }
        
        // Vérifier les droits d'accès sans lancer d'exception
        if (!$this->isGranted('delete', $inventory)) {
            $this->addFlash('error', 'Vous n\'avez pas les droits nécessaires pour supprimer cet inventaire.');
            return $this->redirectToRoute('app_inventory_index');
        }
        
        if ($inventory->getItems()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cet inventaire car il contient des articles. Veuillez d\'abord supprimer tous les articles.');
            return $this->redirectToRoute('app_inventory_show', ['id' => $inventory->getId()]);
        }
        
        if ($this->isCsrfTokenValid('delete'.$inventory->getId(), $request->request->get('_token'))) {
            $entityManager->remove($inventory);
            $entityManager->flush();
            $this->addFlash('success', 'L\'inventaire a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_inventory_index');
    }
}