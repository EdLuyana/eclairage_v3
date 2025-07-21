<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Location;
use App\Entity\Product;
use App\Entity\ProductSize;
use App\Entity\Season;
use App\Entity\Supplier;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Catégorie
        $category = new Category();
        $category->setName("T-shirt");
        $manager->persist($category);

        // Fournisseur
        $supplier = new Supplier();
        $supplier->setName("Nike");
        $manager->persist($supplier);

        // Collection
        $season = new Season();
        $season->setName("Printemps 2025");
        $manager->persist($season);

        // Magasin
        $location = new Location();
        $location->setName("Magasin Paris");
        $manager->persist($location);

        // Produit
        $product = new Product();
        $product->setName("Classic Tee");
        $product->setColor("Blanc");
        $product->setPrice(29.99);
        $product->setCategory($category);
        $product->setSupplier($supplier);
        $product->setSeason($season);
        $manager->persist($product);

        // Tailles
        $sizes = ['S', 'M', 'L'];
        foreach ($sizes as $sizeValue) {
            $size = new ProductSize();
            $size->setValue($sizeValue);
            $size->setProduct($product);
            $manager->persist($size);
        }
        $manager->flush();
    }

}
