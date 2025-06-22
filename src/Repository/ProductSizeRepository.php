<?php

namespace App\Repository;

use App\Entity\ProductSize;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductSizeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductSize::class);
    }

    // Possibilité d'ajouter ici une méthode du type :
    // -> findByProduct(Product $product)
}
