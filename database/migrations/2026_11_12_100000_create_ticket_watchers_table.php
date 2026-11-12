<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Beteiligte of IT tickets (2026-11-12, App\Support\TicketWatchers).
 *
 * Colleagues the creator added to an IT ticket. They see it under Meine
 * Tickets, may open, comment and restore it, and get the same notifications
 * as the creator. Only the creator adds / removes them. IT tickets only (not
 * Handwerk / Korso). Rows go with the ticket when it is deleted for good.
 * Additive only.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_watchers')) {
            return;
        }
        Schema::create('ticket_watchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('added_by')->nullable(); // the creator (or Super_Admin creating for someone)
            $table->timestamps();
            $table->unique(['ticket_id', 'user_id']);
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_watchers');
    }
};
