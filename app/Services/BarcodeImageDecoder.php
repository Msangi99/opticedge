<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use TarfinLabs\ZbarPhp\Exceptions\ZbarError;
use TarfinLabs\ZbarPhp\Zbar as TarfinZbar;
use Zxing\QrReader;

/**
 * Decode barcodes from an image:
 * - khanamiryan/qrcode-detector-decoder (Composer, pure PHP + GD): QR codes without zbar.
 * - tarfin-labs/zbar-php (Composer): wraps zbarimg for one symbol.
 * - Symfony Process → zbarimg: multiple symbols / fallback (still needs zbarimg on PATH for linear codes).
 */
class BarcodeImageDecoder
{
    public function __construct(
        private ?string $binaryPath = null
    ) {
        $this->binaryPath = $binaryPath ?? config('services.zbar.binary', 'zbarimg');
    }

    /**
     * @return array<int, array{code: string, type: string|null}>
     */
    public function decodeFile(string $absolutePath): array
    {
        if (! is_readable($absolutePath)) {
            return [];
        }

        $byCode = [];

        foreach ($this->decodeQrWithZxing($absolutePath) as $row) {
            $byCode[$row['code']] = $row;
        }

        foreach ($this->decodeWithTarfinZbar($absolutePath) as $row) {
            $byCode[$row['code']] = $row;
        }

        foreach ($this->decodeWithZbarProcess($absolutePath) as $row) {
            $byCode[$row['code']] = $row;
        }

        return array_values($byCode);
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

    /**
     * @return array<int, array{code: string, type: string|null}>
     */
    /**
     * @return array<int, array{code: string, type: string|null}>
     */
    private function decodeWithTarfinZbar(string $absolutePath): array
    {
        if (! self::zbarCliAvailable()) {
            return [];
        }

        try {
            $zbar = new TarfinZbar($absolutePath);
            $bar = $zbar->decode();

            return [['code' => $bar->code(), 'type' => $bar->type()]];
        } catch (ZbarError|\Throwable) {
            return [];
        }
    }

    /**
     * @return array<int, array{code: string, type: string|null}>
     */
    private function decodeWithZbarProcess(string $absolutePath): array
    {
        if (! self::zbarCliAvailable()) {
            return [];
        }

        $results = [];

        $process = new Process([$this->binaryPath, '-q', '--raw', $absolutePath]);
        $process->setTimeout(60);
        $process->run();

        $raw = trim($process->getOutput());
        if ($raw !== '') {
            foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $results[$line] = ['code' => $line, 'type' => null];
                }
            }
        }

        if ($results === []) {
            $process2 = new Process([$this->binaryPath, '-q', $absolutePath]);
            $process2->setTimeout(60);
            $process2->run();
            $out = trim($process2->getOutput());
            foreach (preg_split('/\r\n|\r|\n/', $out) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                if (str_contains($line, ':')) {
                    [$type, $code] = explode(':', $line, 2);
                    $code = trim($code);
                    if ($code !== '') {
                        $results[$code] = ['code' => $code, 'type' => trim($type)];
                    }
                } else {
                    $results[$line] = ['code' => $line, 'type' => null];
                }
            }
        }

        return array_values($results);
    }

    public static function zbarCliAvailable(?string $binaryPath = null): bool
    {
        $bin = $binaryPath ?? config('services.zbar.binary', 'zbarimg');
        $p = new Process(['which', $bin]);
        $p->run();

        return $p->isSuccessful() && trim($p->getOutput()) !== '';
    }

    /**
     * GD decodes QR; zbarimg decodes typical device barcodes (Code128, etc.).
     */
    public static function decodingAvailable(): bool
    {
        return extension_loaded('gd') || self::zbarCliAvailable();
    }
}
