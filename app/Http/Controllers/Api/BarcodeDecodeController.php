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
                'message' => 'QR decode needs the PHP GD extension. Linear barcodes are read on the device in the app (no server binary required).',
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
                'message' => 'No QR code found in this image. For 1D barcodes (Code128, IMEI labels), use Scan from photo in the app — codes are detected on your phone.',
                'data' => [],
            ], 422);
        }

        return response()->json([
            'data' => $decoded,
        ]);
    }
}
