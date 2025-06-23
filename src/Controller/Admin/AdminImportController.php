<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Supplier;
use App\Entity\Season;
use App\Form\CsvImportForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminImportController extends AbstractController
{
    #[Route('/admin/import', name: 'admin_import_csv')]
    public function importCsv(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(CsvImportForm::class);
        $form->handleRequest($request);

        $imported = 0;
        $errors = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csvFile')->getData();

            if ($file) {
                try {
                    $path = $file->getPathname();
                    $handle = fopen($path, 'r');

                    // Skip header
                    fgetcsv($handle, 1000, ';');

                    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                        [$name, $color, $reference, $price, $size, $categoryName, $supplierName, $seasonName] = $data;

                        if (!$reference || !$name || !$supplierName || !$seasonName) {
                            $errors[] = "Ligne invalide (référence, nom, fournisseur ou saison manquant).";
                            continue;
                        }

                        // Vérifie si le produit existe déjà (évite doublons)
                        $existing = $em->getRepository(Product::class)->findOneBy(['reference' => $reference, 'size' => $size]);
                        if ($existing) {
                            $errors[] = "Produit $reference ($size) déjà existant.";
                            continue;
                        }

                        // Relations
                        $category = $em->getRepository(Category::class)->findOneBy(['name' => $categoryName]);
                        if (!$category && $categoryName) {
                            $category = new Category();
                            $category->setName($categoryName);
                            $em->persist($category);
                        }

                        $supplier = $em->getRepository(Supplier::class)->findOneBy(['name' => $supplierName]);
                        if (!$supplier) {
                            $supplier = new Supplier();
                            $supplier->setName($supplierName);
                            $em->persist($supplier);
                        }

                        $season = $em->getRepository(Season::class)->findOneBy(['name' => $seasonName]);
                        if (!$season) {
                            $season = new Season();
                            $season->setName($seasonName);
                            $em->persist($season);
                        }

                        $product = new Product();
                        $product->setName($name);
                        $product->setColor($color);
                        $product->setReference($reference);
                        $product->setSize($size);
                        $product->setPrice((float) $price);
                        $product->setCategory($category);
                        $product->setSupplier($supplier);
                        $product->setSeason($season);

                        $em->persist($product);
                        $imported++;
                    }

                    fclose($handle);
                    $em->flush();

                    $this->addFlash('success', "$imported produit(s) importé(s).");
                } catch (FileException|\Exception $e) {
                    $errors[] = "Erreur : " . $e->getMessage();
                }
            }
        }

        return $this->render('admin/import/import.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }
}
