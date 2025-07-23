<?php

namespace App\Repository;

use App\Entity\ReassortLine;
use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReassortLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReassortLine::class);
    }

    /**
     * Retourne une liste de références uniques de produits à intégrer pour un magasin donné.
     */
    public function findDistinctReferencesToIntegrate(Location $location): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('DISTINCT p.reference')
            ->join('r.product', 'p')
            ->where('r.status = :status')
            ->andWhere('r.location = :location')
            ->setParameter('status', 'TO_INTEGRATE')
            ->setParameter('location', $location);

        $results = $qb->getQuery()->getScalarResult();

        return array_column($results, 'reference');
    }

    /**
     * Retourne les tailles disponibles à intégrer pour une référence et un magasin donnés.
     */
    public function findSizesForReferenceToIntegrate(string $reference, Location $location): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.size')
            ->join('r.product', 'p')
            ->where('p.reference = :reference')
            ->andWhere('r.status = :status')
            ->andWhere('r.location = :location')
            ->setParameter('reference', $reference)
            ->setParameter('status', 'TO_INTEGRATE')
            ->setParameter('location', $location);

        $results = $qb->getQuery()->getScalarResult();

        return array_column($results, 'size');
    }
}
