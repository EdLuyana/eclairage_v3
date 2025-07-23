<?php

namespace App\Service;

use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeGeneratorService
{
    public function generate(string $text): string
    {
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($text, $generator::TYPE_CODE_128);

        return 'data:image/png;base64,' . base64_encode($barcode);
    }
}
