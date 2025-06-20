<?php

namespace App\Security;

use App\Entity\Inventory;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InventoryVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

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
        // Si l'utilisateur peut éditer, il peut aussi voir
        if ($this->canEdit($inventory, $user)) {
            return true;
        }

        // Logique supplémentaire pour le partage d'inventaires (à implémenter plus tard)
        return false;
    }

    private function canEdit(Inventory $inventory, User $user): bool
    {
        // Seul le propriétaire peut modifier un inventaire
        return $user === $inventory->getOwner();
    }

    private function canDelete(Inventory $inventory, User $user): bool
    {
        // Seul le propriétaire peut supprimer un inventaire
        return $user === $inventory->getOwner();
    }
}