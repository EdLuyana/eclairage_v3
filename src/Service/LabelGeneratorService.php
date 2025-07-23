<?php

namespace App\Service;

use App\Entity\ReassortLine;
use Dompdf\Dompdf;
use Dompdf\Options;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Twig\Environment;

class LabelGeneratorService
{
    private Environment $twig;
    private BarcodeGeneratorPNG $barcodeGenerator;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->barcodeGenerator = new BarcodeGeneratorPNG();
    }

    public function generateFromReassortLines(array $lines): string
    {
        $grouped = [];

        foreach ($lines as $line) {
            /** @var ReassortLine $line */
            $location = $line->getLocation()->getName();
            $product = $line->getProduct();
            $reference = $product->getReference();
            $size = $line->getSize();
            $price = $product->getPrice();
            $qty = $line->getQuantity();

            $barcodeContent = "{$reference}-{$size}";
            $barcodeImage = $this->barcodeGenerator->getBarcode(
                $barcodeContent,
                $this->barcodeGenerator::TYPE_CODE_128
            );
            $barcodeBase64 = base64_encode($barcodeImage);

            for ($i = 0; $i < $qty; $i++) {
                $grouped[$location][] = [
                    'reference' => $reference,
                    'size' => $size,
                    'price' => number_format($price, 2, ',', ' ') . ' €',
                    'barcode' => $barcodeBase64,
                ];
            }
        }

        // Génération HTML avec Twig
        $html = $this->twig->render('admin/labels/print.html.twig', [
            'groupedLabels' => $grouped
        ]);

        // Options Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->setIsRemoteEnabled(true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultPaperSize', 'A4');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait'); // Position explicite
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }
}
