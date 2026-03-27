<?php

namespace App\Services;

use Zxing\QrReader;

/**
 * Decode barcodes from an image using Composer-only dependencies (no zbar binary).
 *
 * khanamiryan/qrcode-detector-decoder (ZXing port): QR codes via PHP GD.
 * Linear barcodes (Code128, EAN, etc.) are not decoded here — the Flutter app uses
 * mobile_scanner.analyzeImage() (ML Kit / Vision) on the device for those.
 */
class BarcodeImageDecoder
{
    /**
     * @return array<int, array{code: string, type: string|null}>
     */
    public function decodeFile(string $absolutePath): array
    {
        if (! is_readable($absolutePath)) {
            return [];
        }

        return $this->decodeQrWithZxing($absolutePath);
    }

    /**
     * @return array<int, array{code: string, type: string|null}>
     */
    private function decodeQrWithZxing(string $absolutePath): array
    {
        if (! extension_loaded('gd')) {
            return [];
        }

        try {
            $reader = new QrReader($absolutePath);
            $text = $reader->text();
            if ($text === false || $text === null || $text === '') {
                return [];
            }

            return [['code' => (string) $text, 'type' => 'QR_CODE']];
        } catch (\Throwable) {
            return [];
        }
    }

    public static function decodingAvailable(): bool
    {
        return extension_loaded('gd');
    }
}
