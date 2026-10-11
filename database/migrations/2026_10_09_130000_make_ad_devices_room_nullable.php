<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Software forms list every PC without asking for the room (2026-10-09), so a
 * PC a user adds there has no room: ad_devices.ad_ou_id becomes nullable.
 * (No foreign key on this column; safe to re-run.)
 */
return new class extends Migration
{
    public function up(): void
    {
        $col = collect(Schema::getColumns('ad_devices'))->firstWhere('name', 'ad_ou_id');
        if ($col && ! $col['nullable']) {
            Schema::table('ad_devices', function (Blueprint $table) {
                $table->unsignedBigInteger('ad_ou_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // stays nullable: devices without a room may exist by then
    }
};
