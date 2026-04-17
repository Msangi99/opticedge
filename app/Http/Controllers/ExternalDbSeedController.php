<?php

namespace App\Http\Controllers;

use Illuminate\Database\Seeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ExternalDbSeedController extends Controller
{
    /**
     * Run db:seed via GET for external callers when ?pass= matches config optic.db_seed_pass (default 1234).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $expected = (string) config('optic.db_seed_pass', '1234');
        $given = (string) $request->query('pass', '');

        if (! hash_equals($expected, $given)) {
            return response()->json([
                'ok' => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        $options = [
            '--force' => true,
            '--no-interaction' => true,
        ];

        $class = $request->query('class');
        if ($class !== null && $class !== '') {
            $class = (string) $class;
            if (! preg_match('/^[A-Za-z0-9\\\\_]+$/', $class)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid class parameter.',
                ], 422);
            }
            if (! str_starts_with($class, 'Database\\Seeders\\')) {
                $class = 'Database\\Seeders\\'.$class;
            }
            if (! class_exists($class)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Seeder class not found.',
                ], 422);
            }
            if (! is_subclass_of($class, Seeder::class)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid seeder class.',
                ], 422);
            }
            $options['--class'] = $class;
        }

        try {
            Artisan::call('db:seed', $options);
            $output = trim(Artisan::output());

            return response()->json([
                'ok' => true,
                'command' => 'db:seed',
                'class' => $options['--class'] ?? 'default',
                'message' => $output !== '' ? $output : 'Seeding finished.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'command' => 'db:seed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
