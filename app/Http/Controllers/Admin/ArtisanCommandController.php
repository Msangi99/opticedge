<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ArtisanCommandController extends Controller
{
    /**
     * Allowed artisan commands (safe maintenance only). No migrate, seed, or destructive commands.
     */
    protected const ALLOWED_COMMANDS = [
        'cache:clear',
        'config:clear',
        'view:clear',
        'route:clear',
        'optimize:clear',
        'event:clear',
        'package:discover',
    ];

    /**
     * Run a whitelisted artisan command via GET /admin/command/{command}.
     * Example: /admin/command/cache:clear
     */
    public function __invoke(Request $request, string $command)
    {
        // Allow URL-safe form: cache_clear or cache-clear → cache:clear
        $command = preg_replace('/:+/', ':', str_replace(['.', '_', '-'], ':', $command));

        if (!in_array($command, self::ALLOWED_COMMANDS, true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Command not allowed.',
                'allowed' => self::ALLOWED_COMMANDS,
            ], 403);
        }

        try {
            Artisan::call($command);
            $output = trim(Artisan::output());

            return response()->json([
                'ok' => true,
                'command' => $command,
                'message' => $output ?: "Command [{$command}] executed successfully.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'command' => $command,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
