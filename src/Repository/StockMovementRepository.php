<?php

namespace App\Repository;

use App\Entity\StockMovement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockMovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockMovement::class);
    }

    /**
     * Retourne le nombre de ventes (par produit + taille) sur les 30 derniers jours.
     */
    public function countSalesLast30DaysIndexed(): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('IDENTITY(s.product) as productId, s.size as size, SUM(m.quantity) as total')
            ->join('m.stock', 's')
            ->where('m.type = :type')
            ->andWhere('m.createdAt >= :startDate')
            ->setParameter('type', 'sale')
            ->setParameter('startDate', new \DateTimeImmutable('-30 days'))
            ->groupBy('productId, s.size');

        $result = $qb->getQuery()->getResult();

        $sales = [];
        foreach ($result as $row) {
            $sales[$row['productId']][$row['size']] = (int) $row['total'];
        }

        return $sales;
    }

    /**
     * Retourne les mouvements hors ventes filtrés (ajout, retour, retrait)
     */
    public function findNonSalesMovementsFiltered(?string $period, ?int $locationId, ?int $userId): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.stock', 's')->addSelect('s')
            ->leftJoin('s.product', 'p')->addSelect('p')
            ->leftJoin('s.location', 'l')->addSelect('l')
            ->leftJoin('m.user', 'u')->addSelect('u')
            ->where('m.type != :sale')
            ->setParameter('sale', 'sale')
            ->orderBy('m.createdAt', 'DESC');

        // Filtre période
        if ($period) {
            $date = match ($period) {
                '24h' => new \DateTimeImmutable('-1 day'),
                '1w'  => new \DateTimeImmutable('-1 week'),
                '1m'  => new \DateTimeImmutable('-1 month'),
                'month' => (new \DateTimeImmutable('first day of this month'))->setTime(0, 0),
                'year' => (new \DateTimeImmutable('first day of January '.date('Y')))->setTime(0, 0),
                default => null,
            };
            if ($date) {
                $qb->andWhere('m.createdAt >= :startDate')->setParameter('startDate', $date);
            }
        }

        // Filtre magasin
        if ($locationId) {
            $qb->andWhere('l.id = :location')->setParameter('location', $locationId);
        }

        // Filtre vendeuse
        if ($userId) {
            $qb->andWhere('u.id = :user')->setParameter('user', $userId);
        }

        return $qb->getQuery()->getResult();
    }
}
