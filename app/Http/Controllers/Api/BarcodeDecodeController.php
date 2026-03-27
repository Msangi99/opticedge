<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BarcodeImageDecoder;
use Illuminate\Http\Request;

class BarcodeDecodeController extends Controller
{
    public function decodeImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if (! BarcodeImageDecoder::decodingAvailable()) {
            return response()->json([
                'message' => 'Barcode decoding needs PHP GD (for QR via Composer) and/or zbarimg on the server PATH for linear barcodes. Install: composer require khanamiryan/qrcode-detector-decoder (done in this project). For Code128/EAN IMEI-style labels, install zbar-tools or set ZBARIMG_BINARY in .env.',
            ], 503);
        }

        $file = $request->file('image');
        $tmp = $file->getRealPath();
        if (! $tmp || ! is_readable($tmp)) {
            return response()->json(['message' => 'Could not read uploaded image.'], 422);
        }

        $decoder = new BarcodeImageDecoder;
        $decoded = $decoder->decodeFile($tmp);

        if ($decoded === []) {
            return response()->json([
                'message' => 'No barcode found in image.',
                'data' => [],
            ], 422);
        }

        return response()->json([
            'data' => $decoded,
        ]);
    }
}
