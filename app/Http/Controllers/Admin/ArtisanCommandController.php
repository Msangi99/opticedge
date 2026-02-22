<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ArtisanCommandController extends Controller
{
    /**
     * Allowed artisan commands: cache/config, migrations, and db.
     */
    protected const ALLOWED_COMMANDS = [
        // Cache & config
        'cache:clear',
        'config:clear',
        'view:clear',
        'route:clear',
        'optimize:clear',
        'event:clear',
        'package:discover',
        // Migrations
        'migrate',
        'migrate:status',
        'migrate:rollback',
        'migrate:refresh',
        'migrate:reset',
        'migrate:fresh',
        // Database
        'db:seed',
        'db:wipe',
        'db:show',
        'db:table',
        'db:monitor',
        'db:prune',
        // Migration-related
        'migrate:install',
        'schema:dump',
        // Stock
        'stock:recalc',
    ];

    /**
     * Run a whitelisted artisan command via GET /admin/command/{command}.
     * Optional query params: force=1 (--force), seed=1 (--seed for migrate:fresh/refresh).
     * Example: /admin/command/migrate?force=1
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

        $options = [];
        if ($request->boolean('force')) {
            $options['--force'] = true;
        }
        if ($request->boolean('seed') && in_array($command, ['migrate:fresh', 'migrate:refresh'], true)) {
            $options['--seed'] = true;
        }

        try {
            Artisan::call($command, $options);
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
