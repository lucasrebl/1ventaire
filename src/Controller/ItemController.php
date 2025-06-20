<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function new(Request $request, EntityManagerInterface $entityManager, int $inventory_id, SluggerInterface $slugger): Response
    {
        $inventoryRepository = $entityManager->getRepository(\App\Entity\Inventory::class);
        $inventory = $inventoryRepository->find($inventory_id);
        
        if (!$inventory || $inventory->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        
        $item = new Item();
        $item->setInventory($inventory);
        $form = $this->createForm(ItemType::class, $item, [
            'inventory' => $inventory,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('items_directory'),
                        $newFilename
                    );
                    $item->setImage($newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur si quelque chose se passe pendant l'upload
                }
            }

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
    public function show(Item $item): Response
    {
        if ($item->getInventory()->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('item/show.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Item $item, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if ($item->getInventory()->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ItemType::class, $item, [
            'inventory' => $item->getInventory(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('items_directory'),
                        $newFilename
                    );
                    
                    // Supprimer l'ancienne image si elle existe
                    $oldFilename = $item->getImage();
                    if ($oldFilename) {
                        $oldFilePath = $this->getParameter('items_directory').'/'.$oldFilename;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    $item->setImage($newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur si quelque chose se passe pendant l'upload
                }
            }

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
    public function delete(Request $request, Item $item, EntityManagerInterface $entityManager): Response
    {
        if ($item->getInventory()->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $inventoryId = $item->getInventory()->getId();
            
            // Supprimer l'image si elle existe
            $filename = $item->getImage();
            if ($filename) {
                $filePath = $this->getParameter('items_directory').'/'.$filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $entityManager->remove($item);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_inventory_show', ['id' => $inventoryId]);
        }

        return $this->redirectToRoute('app_item_show', ['id' => $item->getId()]);
    }
    
    #[Route('/expiring-soon', name: 'app_item_expiring_soon', methods: ['GET'])]
    public function expiringSoon(ItemRepository $itemRepository): Response
    {
        return $this->render('item/expiring_soon.html.twig', [
            'items' => $itemRepository->findItemsExpiringSoonByUser($this->getUser()),
        ]);
    }
}