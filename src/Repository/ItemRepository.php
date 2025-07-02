<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 *
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }
    

    
    /**
     * Find items by user
     */
    public function findItemsByUser(User $user)
    {
        return $this->createQueryBuilder('i')
            ->join('i.inventory', 'inv')
            ->andWhere('inv.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Find items expiring soon for a user
     */
    public function findItemsExpiringSoon(User $user, int $days = 30, int $limit = 5): array
    {
        $today = new \DateTime();
        $expiryDateLimit = new \DateTime("+{$days} days");
        
        return $this->createQueryBuilder('i')
            ->join('i.inventory', 'inv')
            ->andWhere('inv.owner = :user')
            ->andWhere('i.expiryDate IS NOT NULL')
            ->andWhere('i.expiryDate > :today')
            ->andWhere('i.expiryDate <= :expiryLimit')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->setParameter('expiryLimit', $expiryDateLimit)
            ->orderBy('i.expiryDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Find expired items for a user
     */
    public function findExpiredItems(User $user, int $limit = null): array
    {
        $today = new \DateTime();
        
        $qb = $this->createQueryBuilder('i')
            ->join('i.inventory', 'inv')
            ->andWhere('inv.owner = :user')
            ->andWhere('i.expiryDate IS NOT NULL')
            ->andWhere('i.expiryDate < :today')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->orderBy('i.expiryDate', 'DESC');
            
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }
}