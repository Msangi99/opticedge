@if(auth()->check() && request()->is('admin*'))
    <x-admin-layout>
        @include('admin.partials.catalog-styles')

        <div class="admin-prod-page">
            <div class="admin-clay-panel overflow-hidden max-w-3xl mx-auto mt-8">
                <div class="admin-prod-form-head">
                    <p class="admin-prod-eyebrow">Access denied</p>
                    <h1 class="admin-prod-form-title">You do not have permission</h1>
                    <p class="admin-prod-form-hint">Your role does not include access to this section.</p>
                </div>
                <div class="admin-prod-form-body">
                    <div class="admin-prod-alert admin-prod-alert--warning mb-4">
                        Contact an administrator to update your role permissions if this access is required.
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="admin-prod-btn-primary">Back to dashboard</a>
                        <a href="{{ url()->previous() }}" class="admin-prod-btn-ghost">Go back</a>
                    </div>
                </div>
            </div>
        </div>
    </x-admin-layout>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>403 | Forbidden</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; background: #f8fafc; color: #1f2937; }
            .wrap { max-width: 720px; margin: 12vh auto; padding: 0 16px; }
            .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 24px; }
            h1 { margin: 0 0 8px; font-size: 24px; }
            p { margin: 0 0 16px; color: #475569; }
            a { display: inline-block; padding: 10px 14px; border-radius: 10px; text-decoration: none; background: #0f172a; color: #fff; }
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="card">
                <h1>You do not have permission</h1>
                <p>You are not allowed to access this page.</p>
                <a href="{{ url('/') }}">Go to home</a>
            </div>
        </div>
    </body>
    </html>
@endif
