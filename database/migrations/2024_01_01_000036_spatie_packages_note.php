<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SPATIE PACKAGES NOTE:
 * ------------------------------------------------------------------
 * The following Spatie packages create their own migration tables
 * automatically when you run: php artisan vendor:publish
 *
 * 1. spatie/laravel-permission
 *    Tables: roles, permissions, model_has_roles,
 *            model_has_permissions, role_has_permissions
 *    Command: php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
 *
 * 2. spatie/laravel-activitylog
 *    Table: activity_log
 *    Command: php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
 *
 * 3. spatie/laravel-medialibrary
 *    Table: media
 *    Command: php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
 *
 * 4. spatie/laravel-settings
 *    Table: settings
 *    Command: php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider" --tag="migrations"
 *
 * Run: php artisan migrate  after publishing Spatie migrations.
 * ------------------------------------------------------------------
 *
 * This migration is intentionally a no-op stub to document the above.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Spatie migrations are published via vendor:publish — see docblock above.
    }

    public function down(): void
    {
        // Nothing to roll back here.
    }
};
