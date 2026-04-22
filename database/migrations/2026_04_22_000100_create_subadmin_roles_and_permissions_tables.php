<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subadmin_roles')) {
            Schema::create('subadmin_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('system_key')->nullable()->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subadmin_role_permissions')) {
            Schema::create('subadmin_role_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subadmin_role_id')->constrained('subadmin_roles')->cascadeOnDelete();
                $table->string('module');
                $table->string('action');
                $table->timestamps();

                $table->unique(['subadmin_role_id', 'module', 'action'], 'subadmin_role_permissions_unique');
            });
        }

        if (! Schema::hasColumn('users', 'subadmin_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('subadmin_role_id')->nullable()->constrained('subadmin_roles')->nullOnDelete();
            });
        }

        $fullRole = DB::table('subadmin_roles')->where('system_key', 'fullaccess')->first();
        $fullRoleId = $fullRole?->id;
        if (! $fullRoleId) {
            $fullRoleId = DB::table('subadmin_roles')->insertGetId([
                'name' => 'Full Access',
                'system_key' => 'fullaccess',
                'description' => 'Can access all subadmin modules and actions.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $viewRole = DB::table('subadmin_roles')->where('system_key', 'view')->first();
        $viewRoleId = $viewRole?->id;
        if (! $viewRoleId) {
            $viewRoleId = DB::table('subadmin_roles')->insertGetId([
                'name' => 'View Only',
                'system_key' => 'view',
                'description' => 'Can view pages and reports, but cannot mutate data.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasColumn('users', 'subadmin_role_id')) {
            if (Schema::hasColumn('users', 'ability')) {
                DB::table('users')
                    ->where('role', 'subadmin')
                    ->where(function ($q) {
                        $q->whereNull('ability')->orWhere('ability', 'fullaccess');
                    })
                    ->update(['subadmin_role_id' => $fullRoleId]);

                DB::table('users')
                    ->where('role', 'subadmin')
                    ->where('ability', 'view')
                    ->update(['subadmin_role_id' => $viewRoleId]);
            } else {
                DB::table('users')
                    ->where('role', 'subadmin')
                    ->whereNull('subadmin_role_id')
                    ->update(['subadmin_role_id' => $fullRoleId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subadmin_role_id');
        });

        Schema::dropIfExists('subadmin_role_permissions');
        Schema::dropIfExists('subadmin_roles');
    }
};
