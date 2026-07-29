<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddSecretariatForwardingMailboxes extends Migration
{
    /**
     * Add shared secretariat mailboxes as selectable forwarding users.
     *
     * Existing directory users with the same email address are retained and
     * automatically used by the forwarding form.
     */
    public function up()
    {
        foreach (config('forwarding.mailboxes', []) as $email => $label) {
            if (DB::table('users')->whereRaw('LOWER(email) = ?', [Str::lower($email)])->exists()) {
                continue;
            }

            DB::table('users')->insert([
                'name' => $label,
                'vorname' => null,
                'username' => 'forwarding.' . Str::lower(Str::before($email, '@')),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'domain' => 'forwarding-mailbox',
                'status' => 'Inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Remove only mailbox records created by this migration.
     */
    public function down()
    {
        DB::table('users')->where('domain', 'forwarding-mailbox')->delete();
    }
}
