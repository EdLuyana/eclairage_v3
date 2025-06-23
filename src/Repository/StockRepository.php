<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    /**
     * Récupère tous les stocks avec produit, taille et magasin (jointures optimisées).
     */
    public function findAllWithProductAndSize(): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.product', 'p')
            ->addSelect('p')
            ->join('s.location', 'l')
            ->addSelect('l')
            ->orderBy('p.reference', 'ASC')
            ->addOrderBy('s.size', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les stocks pour un magasin donné (avec produit et taille).
     */
    public function findByLocationWithProductAndSize(int $locationId): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.product', 'p')
            ->addSelect('p')
            ->join('s.location', 'l')
            ->addSelect('l')
            ->where('l.id = :locationId')
            ->setParameter('locationId', $locationId)
            ->orderBy('p.reference', 'ASC')
            ->addOrderBy('s.size', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
