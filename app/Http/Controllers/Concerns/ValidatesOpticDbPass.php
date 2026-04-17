<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ValidatesOpticDbPass
{
    protected function opticDbPassFailed(Request $request): ?JsonResponse
    {
        $expected = (string) config('optic.db_seed_pass', '1234');
        $given = (string) $request->query('pass', '');
        if (! hash_equals($expected, $given)) {
            return response()->json([
                'ok' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        return null;
    }
}
