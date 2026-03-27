<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZbarImageDecoder;
use Illuminate\Http\Request;
class BarcodeDecodeController extends Controller
{
    public function decodeImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if (! ZbarImageDecoder::binaryAvailable()) {
            return response()->json([
                'message' => 'Barcode decoding is not available on this server (install zbar-tools and ensure zbarimg is in PATH), or set ZBARIMG_BINARY in .env.',
            ], 503);
        }

        $file = $request->file('image');
        $tmp = $file->getRealPath();
        if (! $tmp || ! is_readable($tmp)) {
            return response()->json(['message' => 'Could not read uploaded image.'], 422);
        }

        $decoder = new ZbarImageDecoder;
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
