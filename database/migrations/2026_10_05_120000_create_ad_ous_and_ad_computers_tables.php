<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fresh import of rooms (AD OUs) and computers (AD computer objects) via
 * LdapRecord - `php artisan ad:import-inventory` (2026-10-05).
 * New tables only; inv_rooms / inv_items stay untouched until the app is
 * switched over to these.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_ous', function (Blueprint $table) {
            $table->id();
            $table->string('guid', 36)->unique();          // objectGUID - stays the same when the OU is renamed/moved
            $table->foreignId('parent_id')->nullable()->index(); // parent OU (null = top level under the import base)
            $table->string('name');                        // ou
            $table->string('dn', 1000);                    // distinguishedName
            $table->unsignedSmallInteger('depth')->default(0); // 0 = directly under the base
            $table->string('description')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();                         // gone from AD
        });

        Schema::create('ad_computers', function (Blueprint $table) {
            $table->id();
            $table->string('guid', 36)->unique();          // objectGUID
            $table->foreignId('ad_ou_id')->nullable()->index(); // OU (= room) the computer is in
            $table->string('name')->index();               // cn (computer name)
            $table->string('dn', 1000);
            $table->string('dns_hostname')->nullable();
            $table->string('os')->nullable();
            $table->string('os_version')->nullable();
            $table->string('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_logon_at')->nullable();  // lastLogonTimestamp (replicated, ~14 days accurate)
            $table->timestamp('ad_created_at')->nullable();  // whenCreated
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();                         // gone from AD
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_computers');
        Schema::dropIfExists('ad_ous');
    }
};
