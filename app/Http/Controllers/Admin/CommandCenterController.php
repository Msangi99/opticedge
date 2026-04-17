<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CommandCenterController extends Controller
{
    private function trackedExtensionsPath(): string
    {
        return storage_path('app/command_tracked_extensions.json');
    }

    /**
     * @return list<string>
     */
    private function getTrackedExtensions(): array
    {
        $path = $this->trackedExtensionsPath();
        if (! is_file($path)) {
            return [];
        }
        $j = json_decode((string) file_get_contents($path), true);

        return is_array($j) ? array_values(array_unique(array_filter(array_map('strtolower', $j)))) : [];
    }

    public function index()
    {
        $allowedCommands = ArtisanCommandController::allowedCommands();

        $migrationFiles = collect(glob(base_path('database/migrations/*.php')) ?: [])
            ->map(fn ($p) => basename((string) $p))
            ->sort()
            ->values()
            ->all();

        $migrateStatus = '';
        try {
            Artisan::call('migrate:status', ['--no-interaction' => true]);
            $migrateStatus = trim(Artisan::output());
        } catch (\Throwable $e) {
            $migrateStatus = 'Error: '.$e->getMessage();
        }

        // Same PHP process as this web request — extensions actually loaded on this server
        $extensions = array_values(array_unique(array_map('strtolower', get_loaded_extensions())));
        sort($extensions);

        $trackedExtensions = $this->getTrackedExtensions();

        $phpVersion = PHP_VERSION;
        $phpSapi = PHP_SAPI;

        return view('admin.command-center', compact(
            'allowedCommands',
            'migrationFiles',
            'migrateStatus',
            'extensions',
            'trackedExtensions',
            'phpVersion',
            'phpSapi'
        ));
    }

    public function execute(Request $request)
    {
        $validated = $request->validate([
            'command' => 'required|string|max:128',
            'force' => 'nullable|boolean',
            'seed' => 'nullable|boolean',
        ]);

        $command = ArtisanCommandController::resolveAllowedCommand($validated['command']);
        if ($command === null) {
            return redirect()->back()->withInput()->withErrors(['command' => 'That command is not allowed.']);
        }

        $options = ['--no-interaction' => true];
        if (! empty($validated['force'])) {
            $options['--force'] = true;
        }
        if (! empty($validated['seed']) && in_array($command, ['migrate:fresh', 'migrate:refresh'], true)) {
            $options['--seed'] = true;
        }

        try {
            Artisan::call($command, $options);
            $output = trim(Artisan::output());

            return redirect()->back()->with(
                'success',
                $output !== '' ? $output : "Command [{$command}] finished."
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->withErrors(['command' => $e->getMessage()]);
        }
    }

    public function migratePath(Request $request)
    {
        $validated = $request->validate([
            'migration' => 'required|string|max:255',
        ]);

        $base = basename($validated['migration']);
        if (! preg_match('/^[a-zA-Z0-9_\-]+\.php$/', $base)) {
            return redirect()->back()->withErrors(['migration' => 'Invalid migration filename.']);
        }

        $full = base_path('database/migrations/'.$base);
        if (! is_file($full)) {
            return redirect()->back()->withErrors(['migration' => 'Migration file not found in database/migrations.']);
        }

        $relative = 'database/migrations/'.$base;

        try {
            Artisan::call('migrate', [
                '--path' => $relative,
                '--force' => true,
                '--no-interaction' => true,
            ]);
            $output = trim(Artisan::output());

            return redirect()->back()->with(
                'success',
                $output !== '' ? $output : "Ran migration: {$base}"
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['migration' => $e->getMessage()]);
        }
    }

    public function trackExtension(Request $request)
    {
        $validated = $request->validate([
            'extension' => 'required|string|max:64|regex:/^[a-zA-Z0-9_]+$/',
        ]);

        $name = strtolower($validated['extension']);
        $list = $this->getTrackedExtensions();
        if (! in_array($name, $list, true)) {
            $list[] = $name;
            sort($list);
            File::put($this->trackedExtensionsPath(), json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return redirect()->back()->with('success', "Tracking extension: {$name}");
    }

    public function untrackExtension(Request $request)
    {
        $validated = $request->validate([
            'extension' => 'required|string|max:64',
        ]);

        $name = strtolower($validated['extension']);
        $list = array_values(array_filter($this->getTrackedExtensions(), fn ($e) => $e !== $name));
        File::put($this->trackedExtensionsPath(), json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return redirect()->back()->with('success', "Removed {$name} from tracked list.");
    }
}
