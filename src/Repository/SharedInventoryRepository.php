<?php

namespace App\Repository;

use App\Entity\SharedInventory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SharedInventory>
 *
 * @method SharedInventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SharedInventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SharedInventory[]    findAll()
 * @method SharedInventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SharedInventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SharedInventory::class);
    }

    /**
     * Find all inventories shared with a specific user
     */
    public function findSharedWithUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.inventory', 'i')
            ->where('s.sharedWith = :user')
            ->setParameter('user', $user)
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
