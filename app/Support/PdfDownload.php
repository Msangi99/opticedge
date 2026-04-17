<?php

namespace App\Support;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

final class PdfDownload
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromView(string $view, array $data, string $filename): Response
    {
        return self::fromHtml(view($view, $data)->render(), $filename);
    }

    public static function fromHtml(string $html, string $filename): Response
    {
        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
