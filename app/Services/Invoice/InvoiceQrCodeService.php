<?php

namespace App\Services\Invoice;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class InvoiceQrCodeService
{
    public static function generateQrCode(string $data): string
    {
        $qrCode = new \Endroid\QrCode\QrCode(
            data: $data,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Low,
            size: 200,
            margin: 10,
            roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
            foregroundColor: new \Endroid\QrCode\Color\Color(0, 0, 0),
            backgroundColor: new \Endroid\QrCode\Color\Color(255, 255, 255)
        );
        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
}
