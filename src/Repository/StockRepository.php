<?php

namespace App\Repository;

use App\Entity\Stock;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use App\Entity\Location;

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
    public function getStockMapByProduct(Product $product): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT l.name AS location_name, s.size, s.quantity
        FROM stock s
        INNER JOIN location l ON s.location_id = l.id
        WHERE s.product_id = :productId
        ORDER BY l.name ASC, s.size ASC
    ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['productId' => $product->getId()]);

        $rows = $resultSet->fetchAllAssociative();
        $stockMap = [];

        foreach ($rows as $row) {
            $location = strtoupper($row['location_name']);
            $size = $row['size'];
            $qty = (int)$row['quantity'];

            if (!isset($stockMap[$location])) {
                $stockMap[$location] = [];
            }

            $stockMap[$location][$size] = $qty;
        }


    return $stockMap;
}
    public function findAvailableSizesForProductAndLocation(Product $product, Location $location): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.size')
            ->where('s.product = :product')
            ->andWhere('s.location = :location')
            ->andWhere('s.quantity > 0')
            ->setParameter('product', $product)
            ->setParameter('location', $location)
            ->orderBy('s.size', 'ASC');

        $results = $qb->getQuery()->getScalarResult(); // [['size' => 'S'], ['size' => 'M']...]

        return array_column($results, 'size');
    }
public function findReferencesInLocationByTerm(string $term, Location $location): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('DISTINCT p.reference')
            ->join('s.product', 'p')
            ->where('s.location = :location')
            ->andWhere('s.quantity > 0')
            ->andWhere('p.reference LIKE :term')
            ->setParameter('location', $location)
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('p.reference', 'ASC');

        $results = $qb->getQuery()->getScalarResult();

        return array_column($results, 'reference');
    }

}
