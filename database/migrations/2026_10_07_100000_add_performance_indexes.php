<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance (2026-10-07): indexes for the queries every ticket list, the
 * Dashboard and the notification bell run. Before this, tickets / handwerks /
 * korsos had NO index besides the primary key, so every list, count and
 * "my tickets" query read the whole table, and the unread-notifications query
 * (shared on every page) sorted all of a user's notifications.
 *
 * Indexes only - no data or column is changed. Each one is skipped if it
 * already exists, so re-running on a re-copied DB is safe.
 */
return new class extends Migration
{
    private array $indexes = [
        'tickets' => [
            // open lists: deleted_at IS NULL [AND on_location IS NULL] ORDER BY created_at
            'tickets_open_list_idx' => ['deleted_at', 'on_location', 'created_at'],
            'tickets_submitter_idx' => ['submitter'],
            'tickets_assignedto_idx' => ['assignedTo'],
            // Email Weiterleitung (Dashboard, header counts, /email-forwardings)
            'tickets_problem_type_idx' => ['problem_type', 'deleted_at'],
        ],
        'handwerks' => [
            'handwerks_standort_idx' => ['submitter_standort', 'deleted_at'],
            'handwerks_submitter_idx' => ['submitter'],
            'handwerks_assignedto_idx' => ['assignedTo'],
        ],
        'korsos' => [
            'korsos_submitter_idx' => ['submitter'],
            'korsos_assignedto_idx' => ['assignedTo'],
            'korsos_status_idx' => ['deleted_at', 'ticket_status_id'],
        ],
        'notifications' => [
            // unread notifications of one user, newest first (every page + Dashboard)
            'notifications_unread_idx' => ['notifiable_type', 'notifiable_id', 'read_at', 'created_at'],
        ],
        'comments' => [
            'comments_deleted_idx' => ['commentable_type', 'commentable_id', 'deleted_at'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name) || ! Schema::hasColumns($table, $columns)) {
                    continue;
                }
                Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if (Schema::hasTable($table) && Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }
};
