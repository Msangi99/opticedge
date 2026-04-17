<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesOpticDbPass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ExternalDbMigrateController extends Controller
{
    use ValidatesOpticDbPass;

    /**
     * Run migrate via GET when ?pass= matches config optic.db_seed_pass (default 1234).
     */
    public function __invoke(Request $request): JsonResponse
    {
        if ($deny = $this->opticDbPassFailed($request)) {
            return $deny;
        }

        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--no-interaction' => true,
            ]);
            $output = trim(Artisan::output());

            return response()->json([
                'ok' => true,
                'command' => 'migrate',
                'message' => $output !== '' ? $output : 'Migrations finished.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'command' => 'migrate',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
