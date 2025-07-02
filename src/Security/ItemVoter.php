<?php

namespace App\Security;

use App\Entity\Item;
use App\Entity\SharedInventory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ItemVoter extends Voter
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

        if (!$subject instanceof Item) {
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

        /** @var Item $item */
        $item = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($item, $user),
            self::EDIT => $this->canEdit($item, $user),
            self::DELETE => $this->canDelete($item, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(Item $item, User $user): bool
    {
        // Si l'utilisateur est le propriétaire de l'inventaire, il peut voir l'article
        if ($user === $item->getInventory()->getOwner()) {
            return true;
        }

        // Vérifie si l'inventaire est partagé avec l'utilisateur
        $sharedInventory = $this->entityManager->getRepository(SharedInventory::class)->findOneBy([
            'inventory' => $item->getInventory(),
            'sharedWith' => $user
        ]);

        // Si l'inventaire est partagé avec l'utilisateur, il peut voir l'article
        return $sharedInventory !== null;
    }

    private function canEdit(Item $item, User $user): bool
    {
        // Si l'utilisateur est le propriétaire de l'inventaire, il peut modifier l'article
        if ($user === $item->getInventory()->getOwner()) {
            return true;
        }

        // Vérifie si l'inventaire est partagé avec l'utilisateur avec des droits d'édition
        $sharedInventory = $this->entityManager->getRepository(SharedInventory::class)->findOneBy([
            'inventory' => $item->getInventory(),
            'sharedWith' => $user
        ]);

        if (!$sharedInventory) {
            return false;
        }

        // L'utilisateur peut modifier l'article si le niveau d'accès est 'edit' ou 'admin'
        return in_array($sharedInventory->getAccessLevel(), ['edit', 'admin']);
    }

    private function canDelete(Item $item, User $user): bool
    {
        // Si l'utilisateur est le propriétaire de l'inventaire, il peut supprimer l'article
        if ($user === $item->getInventory()->getOwner()) {
            return true;
        }

        // Vérifie si l'inventaire est partagé avec l'utilisateur avec des droits admin
        $sharedInventory = $this->entityManager->getRepository(SharedInventory::class)->findOneBy([
            'inventory' => $item->getInventory(),
            'sharedWith' => $user
        ]);

        if (!$sharedInventory) {
            return false;
        }

        // L'utilisateur peut supprimer l'article seulement si le niveau d'accès est 'admin'
        return $sharedInventory->getAccessLevel() === 'admin';
    }
}
