<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Queue table for background mails (QUEUE_CONNECTION=database, 2026-10-12).
 *
 * Laravel's default name "jobs" is already taken in this app by an old
 * project/task table (2022_02_04_111832_create_jobs_table), so the queue
 * uses "queue_jobs" (config/queue.php). Standard Laravel schema. Additive.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('queue_jobs')) {
            return;
        }
        Schema::create('queue_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_jobs');
    }
};
