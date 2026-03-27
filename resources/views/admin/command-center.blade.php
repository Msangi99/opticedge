<x-admin-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Command center</h2>
        <p class="mt-1 text-sm text-slate-500">Run whitelisted Artisan commands, migrate a single migration file, and review PHP extensions.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <pre class="whitespace-pre-wrap font-sans">{{ session('success') }}</pre>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Allowed commands --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-3">Allowed Artisan commands</h3>
            <ul class="mb-4 max-h-48 overflow-y-auto custom-scrollbar text-sm text-slate-600 space-y-1 border border-slate-100 rounded-md p-3 bg-slate-50">
                @foreach ($allowedCommands as $cmd)
                    <li><code class="text-slate-800">{{ $cmd }}</code></li>
                @endforeach
            </ul>

            <form action="{{ route('command.execute') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Command</label>
                    <select name="command" class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900] sm:text-sm">
                        @foreach ($allowedCommands as $cmd)
                            <option value="{{ $cmd }}" @selected(old('command') === $cmd)>{{ $cmd }}</option>
                        @endforeach
                    </select>
                    @error('command')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="force" value="1" class="rounded border-slate-300 text-[#fa8900] focus:ring-[#fa8900]" @checked(old('force'))>
                        <span>--force</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="seed" value="1" class="rounded border-slate-300 text-[#fa8900] focus:ring-[#fa8900]" @checked(old('seed'))>
                        <span>--seed (migrate:fresh / migrate:refresh)</span>
                    </label>
                </div>
                <button type="submit" class="inline-flex items-center rounded-md bg-[#232f3e] px-4 py-2 text-sm font-medium text-white hover:bg-[#19212c]">
                    Run command
                </button>
            </form>
            <p class="mt-3 text-xs text-slate-500">JSON API still available: <code class="text-slate-700">GET /command/{command}</code> (same whitelist).</p>
        </div>

        {{-- Migrations --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-3">Migrations</h3>
            <p class="text-sm text-slate-600 mb-3">Runs <code class="text-slate-800">php artisan migrate --path=database/migrations/&lt;file&gt;</code> for one file.</p>

            <form action="{{ route('command.migrate-path') }}" method="POST" class="space-y-3 mb-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Migration file</label>
                    @if (count($migrationFiles) === 0)
                        <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md p-3">No migration files found under <code class="text-slate-800">database/migrations</code>.</p>
                    @else
                    <select name="migration" class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900] sm:text-sm max-h-40">
                        @foreach ($migrationFiles as $file)
                            <option value="{{ $file }}" @selected(old('migration') === $file)>{{ $file }}</option>
                        @endforeach
                    </select>
                    @endif
                    @error('migration')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" @disabled(count($migrationFiles) === 0) class="inline-flex items-center rounded-md bg-[#fa8900] px-4 py-2 text-sm font-medium text-[#232f3e] hover:bg-[#e67a00] disabled:opacity-50 disabled:cursor-not-allowed">
                    Migrate selected file
                </button>
            </form>

            <h4 class="text-sm font-semibold text-slate-700 mb-2">migrate:status</h4>
            <pre class="text-xs text-slate-700 bg-slate-900 text-slate-100 rounded-md p-3 max-h-64 overflow-auto custom-scrollbar whitespace-pre-wrap font-mono">{{ $migrateStatus }}</pre>
        </div>
    </div>

    {{-- Extensions --}}
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-3">PHP extensions</h3>
        <p class="text-sm text-slate-600 mb-2">Lists what this server’s PHP runtime has <strong>actually loaded</strong> right now (same process as this page). “Track” is only a checklist — it does not install anything; install via your host or package manager (e.g. <code class="text-slate-800">php-gd</code>).</p>
        <p class="text-xs text-slate-500 mb-4">Runtime: PHP {{ $phpVersion }} · SAPI <code class="text-slate-700">{{ $phpSapi }}</code></p>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <h4 class="text-sm font-semibold text-slate-700 mb-1">Loaded on this server</h4>
                <p class="text-xs text-slate-500 mb-2">From <code class="text-slate-700">get_loaded_extensions()</code> — extensions active in this PHP build on the host.</p>
                <ul class="text-sm text-slate-600 max-h-64 overflow-y-auto custom-scrollbar border border-slate-100 rounded-md p-3 bg-slate-50 space-y-0.5">
                    @foreach ($extensions as $ext)
                        <li><code class="text-slate-800">{{ $ext }}</code></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-slate-700 mb-2">Tracked checklist</h4>
                @if (count($trackedExtensions))
                    <ul class="mb-3 space-y-2">
                        @foreach ($trackedExtensions as $ext)
                            <li class="flex items-center justify-between gap-2 text-sm border border-slate-100 rounded-md px-3 py-2 bg-slate-50">
                                <span>
                                    <code class="text-slate-800">{{ $ext }}</code>
                                    @if (in_array($ext, $extensions, true))
                                        <span class="ml-2 text-xs text-emerald-600">loaded</span>
                                    @else
                                        <span class="ml-2 text-xs text-amber-600">not loaded</span>
                                    @endif
                                </span>
                                <form action="{{ route('command.extension-untrack') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="extension" value="{{ $ext }}">
                                    <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500 mb-3">No extensions tracked yet.</p>
                @endif

                <form action="{{ route('command.extension-track') }}" method="POST" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <div class="flex-1 min-w-[8rem]">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Extension name (e.g. gd, intl)</label>
                        <input type="text" name="extension" value="{{ old('extension') }}" placeholder="gd"
                            class="w-full rounded-md border-slate-300 shadow-sm focus:border-[#fa8900] focus:ring-[#fa8900] sm:text-sm">
                    </div>
                    <button type="submit" class="rounded-md bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Add to checklist
                    </button>
                </form>
                @error('extension')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</x-admin-layout>
