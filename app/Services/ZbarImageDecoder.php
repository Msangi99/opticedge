<?php

namespace App\Services;

use Symfony\Component\Process\Process;

/**
 * Decodes barcodes from an image using the zbarimg CLI (zbar-tools package).
 * Install on server: apt install zbar-tools (or equivalent).
 */
class ZbarImageDecoder
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

    public static function binaryAvailable(?string $binaryPath = null): bool
    {
        $bin = $binaryPath ?? config('services.zbar.binary', 'zbarimg');
        $p = new Process(['which', $bin]);
        $p->run();

        return $p->isSuccessful() && trim($p->getOutput()) !== '';
    }
}
