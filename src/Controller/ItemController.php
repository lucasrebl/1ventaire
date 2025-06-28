<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/item')]
#[IsGranted('ROLE_USER')]
class ItemController extends AbstractController
{
    #[Route('/', name: 'app_item_index', methods: ['GET'])]
    public function index(ItemRepository $itemRepository): Response
    {
        return $this->render('item/index.html.twig', [
            'items' => $itemRepository->findItemsByUser($this->getUser()),
        ]);
    }

    #[Route('/new/{inventory_id}', name: 'app_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $inventory_id): Response
    {
        $inventoryRepository = $entityManager->getRepository(\App\Entity\Inventory::class);
        $inventory = $inventoryRepository->find($inventory_id);
        
        if (!$inventory || $inventory->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        
        $item = new Item();
        $item->setInventory($inventory);
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();

            return $this->redirectToRoute('app_inventory_show', ['id' => $inventory_id]);
        }

        return $this->render('item/new.html.twig', [
            'item' => $item,
            'form' => $form,
            'inventory' => $inventory,
        ]);
    }

    #[Route('/{id}', name: 'app_item_show', methods: ['GET'])]
    public function show(string $id, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->find($id);
        
        if (!$item) {
            throw $this->createNotFoundException('L\'article demandé n\'existe pas.');
        }
        
        try {
            $this->denyAccessUnlessGranted('view', $item);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Vous n\'avez pas les droits nécessaires pour voir cet article.');
            return $this->redirectToRoute('app_inventory_show', ['id' => $item->getInventory()->getId()]);
        }
        
        // Récupérer les droits d'accès pour les passer au template
        $canEdit = $this->isGranted('edit', $item);
        $canDelete = $this->isGranted('delete', $item);
        
        // Préparer les messages selon les droits
        $messages = [];
        if (!$canEdit) {
            $messages['edit'] = 'Vous n\'avez pas les droits nécessaires pour modifier cet article.';
        }
        if (!$canDelete) {
            $messages['delete'] = 'Vous n\'avez pas les droits nécessaires pour supprimer cet article.';
        }

        return $this->render('item/show.html.twig', [
            'item' => $item,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'permission_messages' => $messages,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $id, EntityManagerInterface $entityManager, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->find($id);
        
        if (!$item) {
            throw $this->createNotFoundException('L\'article demandé n\'existe pas.');
        }
        
        try {
            $this->denyAccessUnlessGranted('edit', $item);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Vous n\'avez pas les droits nécessaires pour modifier cet article.');
            return $this->redirectToRoute('app_item_show', ['id' => $item->getId()]);
        }

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('app_item_show', ['id' => $item->getId()]);
        }

        return $this->render('item/edit.html.twig', [
            'item' => $item,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_item_delete', methods: ['POST'])]
    public function delete(Request $request, string $id, EntityManagerInterface $entityManager, ItemRepository $itemRepository): Response
    {
        $item = $itemRepository->find($id);
        
        if (!$item) {
            $this->addFlash('error', 'L\'article demandé n\'existe pas.');
            return $this->redirectToRoute('app_inventory_index');
        }
        
        $inventoryId = $item->getInventory()->getId();
        
        if (!$this->isGranted('delete', $item)) {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cet article.');
            return $this->redirectToRoute('app_inventory_show', ['id' => $inventoryId]);
        }

        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($item);
                $entityManager->flush();
                
                $this->addFlash('success', 'L\'article a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'article.');
            }
            
            return $this->redirectToRoute('app_inventory_show', ['id' => $inventoryId]);
        }

        $this->addFlash('error', 'Token CSRF invalide.');
        return $this->redirectToRoute('app_inventory_show', ['id' => $inventoryId]);
    }
    
    #[Route('/delete-expired', name: 'app_item_delete_expired')]
    public function deleteExpired(Request $request, EntityManagerInterface $entityManager, ItemRepository $itemRepository): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        
        if ($this->isCsrfTokenValid('delete_expired', $request->request->get('_token'))) {
            // Récupérer tous les articles expirés de l'utilisateur
            $expiredItems = $itemRepository->findExpiredItems($this->getUser());
            
            $count = count($expiredItems);
            if ($count > 0) {
                foreach ($expiredItems as $item) {
                    $entityManager->remove($item);
                }
                
                $entityManager->flush();
                $this->addFlash('success', "$count article(s) expiré(s) supprimé(s) avec succès.");
            } else {
                $this->addFlash('info', 'Aucun article expiré à supprimer.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        
        return $this->redirectToRoute('app_home');
    }
}