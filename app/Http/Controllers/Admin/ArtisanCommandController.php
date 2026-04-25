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
        'db:fresh-seed',
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
        // MobileAPI catalog (insert-only new devices)
        'catalog:sync-mobileapi',
    ];

    /**
     * @return list<string>
     */
    public static function allowedCommands(): array
    {
        return self::ALLOWED_COMMANDS;
    }

    /**
     * Map request string to a whitelisted command (exact match first, then URL-style normalization).
     */
    public static function resolveAllowedCommand(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        if (in_array($raw, self::ALLOWED_COMMANDS, true)) {
            return $raw;
        }
        $normalized = preg_replace('/:+/', ':', str_replace(['.', '_', '-'], ':', $raw));

        if (in_array($normalized, self::ALLOWED_COMMANDS, true)) {
            return $normalized;
        }

        // URL-style "db-fresh-seed" becomes "db:fresh:seed" — map back to the real Artisan name
        if ($normalized === 'db:fresh:seed') {
            return 'db:fresh-seed';
        }

        return null;
    }

    /**
     * Run a whitelisted artisan command via GET /admin/command/{command}.
     * Optional query params: force=1 (--force), seed=1 (--seed for migrate:fresh/refresh).
     * Example: /admin/command/migrate?force=1
     */
    public function __invoke(Request $request, string $command)
    {
        $command = self::resolveAllowedCommand($command);
        if ($command === null) {
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
