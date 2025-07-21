<?php

namespace App\Repository;

use App\Entity\Size;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Size>
 *
 * @method Size|null find($id, $lockMode = null, $lockVersion = null)
 * @method Size|null findOneBy(array $criteria, array $orderBy = null)
 * @method Size[]    findAll()
 * @method Size[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SizeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Size::class);
    }

    // Exemple de méthode personnalisée (facultative)
    public function findByValue(string $value): ?Size
    {
        return $this->findOneBy(['value' => $value]);
    }
}
