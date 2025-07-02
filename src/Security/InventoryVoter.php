<?php

namespace App\Security;

use App\Entity\Inventory;
use App\Entity\SharedInventory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InventoryVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Inventory) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // l'utilisateur doit être connecté
            return false;
        }

        /** @var Inventory $inventory */
        $inventory = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($inventory, $user),
            self::EDIT => $this->canEdit($inventory, $user),
            self::DELETE => $this->canDelete($inventory, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(Inventory $inventory, User $user): bool
    {
        // Si l'utilisateur est le propriétaire, il peut voir
        if ($user === $inventory->getOwner()) {
            return true;
        }

        // Vérifie si l'inventaire est partagé avec l'utilisateur
        $sharedInventory = $this->entityManager->getRepository(SharedInventory::class)->findOneBy([
            'inventory' => $inventory,
            'sharedWith' => $user
        ]);

        // Si l'inventaire est partagé avec l'utilisateur, il peut le voir
        return $sharedInventory !== null;
    }

    private function canEdit(Inventory $inventory, User $user): bool
    {
        // Si l'utilisateur est le propriétaire, il peut modifier
        if ($user === $inventory->getOwner()) {
            return true;
        }

        // Vérifie si l'inventaire est partagé avec l'utilisateur avec des droits d'édition
        $sharedInventory = $this->entityManager->getRepository(SharedInventory::class)->findOneBy([
            'inventory' => $inventory,
            'sharedWith' => $user
        ]);

        if (!$sharedInventory) {
            return false;
        }

        // L'utilisateur peut modifier si le niveau d'accès est 'edit' ou 'admin'
        return in_array($sharedInventory->getAccessLevel(), ['edit', 'admin']);
    }

    private function canDelete(Inventory $inventory, User $user): bool
    {
        // Si l'utilisateur est le propriétaire, il peut supprimer
        if ($user === $inventory->getOwner()) {
            return true;
        }

        // Vérifie si l'inventaire est partagé avec l'utilisateur avec des droits admin
        $sharedInventory = $this->entityManager->getRepository(SharedInventory::class)->findOneBy([
            'inventory' => $inventory,
            'sharedWith' => $user
        ]);

        if (!$sharedInventory) {
            return false;
        }

        // L'utilisateur peut supprimer seulement si le niveau d'accès est 'admin'
        return $sharedInventory->getAccessLevel() === 'admin';
    }
}