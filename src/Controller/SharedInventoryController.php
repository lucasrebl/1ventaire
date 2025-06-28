<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\SharedInventory;
use App\Form\SharedInventoryType;
use App\Repository\SharedInventoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/shared/inventory')]
#[IsGranted('ROLE_USER')]
class SharedInventoryController extends AbstractController
{
    #[Route('/share/{id}', name: 'app_inventory_share', methods: ['GET', 'POST'])]
    public function share(Request $request, Inventory $inventory, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Vérifie si l'utilisateur est le propriétaire de l'inventaire
        if ($inventory->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas l\'autorisation de partager cet inventaire.');
        }

        $sharedInventory = new SharedInventory();
        $sharedInventory->setInventory($inventory);
        
        $form = $this->createForm(SharedInventoryType::class, $sharedInventory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère l'email depuis le formulaire
            $userEmail = $form->get('userEmail')->getData();
            
            // Recherche de l'utilisateur par email
            $targetUser = $userRepository->findOneBy(['email' => $userEmail]);
            
            if (!$targetUser) {
                $this->addFlash('error', 'Utilisateur non trouvé avec cette adresse e-mail.');
                return $this->redirectToRoute('app_inventory_share', ['id' => $inventory->getId()]);
            }
            
            // Vérifie que l'utilisateur ne partage pas avec lui-même
            if ($targetUser === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas partager un inventaire avec vous-même.');
                return $this->redirectToRoute('app_inventory_share', ['id' => $inventory->getId()]);
            }
            
            // Vérifie si un partage existe déjà
            $existingShare = $entityManager->getRepository(SharedInventory::class)->findOneBy([
                'inventory' => $inventory,
                'sharedWith' => $targetUser
            ]);
            
            if ($existingShare) {
                $this->addFlash('error', 'Cet inventaire est déjà partagé avec cet utilisateur.');
                return $this->redirectToRoute('app_inventory_share', ['id' => $inventory->getId()]);
            }
            
            $sharedInventory->setSharedWith($targetUser);
            
            $entityManager->persist($sharedInventory);
            $entityManager->flush();

            $this->addFlash('success', 'L\'inventaire a été partagé avec succès.');
            return $this->redirectToRoute('app_inventory_show', ['id' => $inventory->getId()]);
        }

        return $this->render('shared_inventory/share.html.twig', [
            'inventory' => $inventory,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/remove/{id}', name: 'app_shared_inventory_remove', methods: ['POST'])]
    public function remove(Request $request, SharedInventory $sharedInventory, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'inventaire pour pouvoir rediriger même en cas d'erreur
        $inventory = $sharedInventory->getInventory();

        try {
            // Vérifie si l'utilisateur est le propriétaire de l'inventaire
            if ($inventory->getOwner() !== $this->getUser()) {
                $this->addFlash('error', 'Vous n\'avez pas l\'autorisation de supprimer ce partage. Seul le propriétaire peut effectuer cette action.');
                return $this->redirectToRoute('app_inventory_show', ['id' => $inventory->getId()]);
            }
            
            if ($this->isCsrfTokenValid('delete'.$sharedInventory->getId(), $request->request->get('_token'))) {
                $entityManager->remove($sharedInventory);
                $entityManager->flush();
                
                $this->addFlash('success', 'Le partage a été supprimé avec succès.');
            } else {
                $this->addFlash('error', 'Token CSRF invalide.');
            }
        } catch (\Exception $e) {
            // Capture toute exception non prévue et affiche un message d'erreur générique
            $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du partage.');
        }

        return $this->redirectToRoute('app_inventory_show', ['id' => $inventory->getId()]);
    }
    
    #[Route('/edit/{id}', name: 'app_shared_inventory_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SharedInventory $sharedInventory, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'inventaire pour pouvoir rediriger même en cas d'erreur
        $inventory = $sharedInventory->getInventory();

        try {
            // Vérifie si l'utilisateur est le propriétaire de l'inventaire
            if ($inventory->getOwner() !== $this->getUser()) {
                // Ajoute un message d'erreur plus convivial au lieu de lancer une exception
                $this->addFlash('error', 'Vous n\'avez pas l\'autorisation de modifier les droits de partage de cet inventaire. Seul le propriétaire peut effectuer cette action.');
                return $this->redirectToRoute('app_inventory_show', ['id' => $inventory->getId()]);
            }
            
            // Crée le formulaire avec seulement le champ accessLevel
            $form = $this->createForm(SharedInventoryType::class, $sharedInventory, [
                'edit_mode' => true // Option pour indiquer qu'on est en mode édition
            ]);
            
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                
                $this->addFlash('success', 'Les droits d\'accès ont été modifiés avec succès.');
                return $this->redirectToRoute('app_inventory_show', ['id' => $inventory->getId()]);
            }
            
            return $this->render('shared_inventory/edit.html.twig', [
                'sharedInventory' => $sharedInventory,
                'form' => $form->createView(),
            ]);

        } catch (\Exception $e) {
            // Capture toute exception non prévue et affiche un message d'erreur générique
            $this->addFlash('error', 'Une erreur s\'est produite lors de la modification des droits de partage.');
            return $this->redirectToRoute('app_inventory_show', ['id' => $inventory->getId()]);
        }
    }
}
