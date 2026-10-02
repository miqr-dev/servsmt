<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log of profile -> Active Directory write-backs (App\Jobs\SyncUserToActiveDirectory):
 * who changed whose profile, which AD attributes changed (old/new), and
 * whether it reached AD. New table only - nothing existing is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();       // whose AD account
            $table->unsignedBigInteger('changed_by')->nullable(); // null = console / retry
            $table->string('status', 20)->default('pending')->index(); // pending|success|failed|not_found
            $table->json('changes')->nullable();                  // {attribute: {old, new}}
            $table->text('error')->nullable();
            $table->string('ad_dn')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_sync_logs');
    }
};
