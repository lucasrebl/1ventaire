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
     * Find items expiring soon
     */
    public function findItemsExpiringSoon(int $days = 7)
    {
        $date = new \DateTime();
        $date->modify('+'.$days.' days');
        
        return $this->createQueryBuilder('i')
            ->andWhere('i.expiryDate IS NOT NULL')
            ->andWhere('i.expiryDate <= :date')
            ->setParameter('date', $date)
            ->orderBy('i.expiryDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Find items expiring soon for a specific user
     */
    public function findItemsExpiringSoonByUser(User $user, int $days = 7)
    {
        $date = new \DateTime();
        $date->modify('+'.$days.' days');
        
        return $this->createQueryBuilder('i')
            ->join('i.inventory', 'inv')
            ->andWhere('inv.owner = :user')
            ->andWhere('i.expiryDate IS NOT NULL')
            ->andWhere('i.expiryDate <= :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->orderBy('i.expiryDate', 'ASC')
            ->getQuery()
            ->getResult();
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
}