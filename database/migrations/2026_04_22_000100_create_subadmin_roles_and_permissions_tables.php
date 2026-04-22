<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subadmin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system_key')->nullable()->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('subadmin_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subadmin_role_id')->constrained('subadmin_roles')->cascadeOnDelete();
            $table->string('module');
            $table->string('action');
            $table->timestamps();

            $table->unique(['subadmin_role_id', 'module', 'action'], 'subadmin_role_permissions_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('subadmin_role_id')->nullable()->after('ability')->constrained('subadmin_roles')->nullOnDelete();
        });

        $fullRoleId = DB::table('subadmin_roles')->insertGetId([
            'name' => 'Full Access',
            'system_key' => 'fullaccess',
            'description' => 'Can access all subadmin modules and actions.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $viewRoleId = DB::table('subadmin_roles')->insertGetId([
            'name' => 'View Only',
            'system_key' => 'view',
            'description' => 'Can view pages and reports, but cannot mutate data.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
