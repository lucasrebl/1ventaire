<?php

namespace App\Controller;

use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(InventoryRepository $inventoryRepository, ItemRepository $itemRepository): Response
    {
        // Check if user is logged in
        if ($this->getUser()) {
            // Fetch data for authenticated users
            $recentInventories = $inventoryRepository->findRecentByUser($this->getUser(), 3);
            $expiringItems = $itemRepository->findItemsExpiringSoon($this->getUser(), 30, 5);
            $expiredItems = $itemRepository->findExpiredItems($this->getUser(), 10); // Limit to 10 items
            
            // Récupérer les inventaires partagés avec l'utilisateur
            $sharedInventories = $inventoryRepository->findSharedWithUser($this->getUser());
            
            return $this->render('home/authenticated.html.twig', [
                'recentInventories' => $recentInventories,
                'expiringItems' => $expiringItems,
                'expiredItems' => $expiredItems,
                'sharedInventories' => $sharedInventories,
            ]);
        }
        
        // Guest view (existing landing page)
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}