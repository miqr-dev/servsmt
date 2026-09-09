<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wires the previously-orphaned permissioncategories table to Spatie's own
 * permissions table, so Permission::with('category') (used by roles.create /
 * roles.edit) actually resolves instead of throwing. Nullable + nullOnDelete
 * so removing a category never breaks existing permissions.
 */
class AddPermissioncategoryIdToPermissionsTable extends Migration
{
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permissioncategory_id')->nullable()->after('guard_name');
            $table->foreign('permissioncategory_id')
                ->references('id')->on('permissioncategories')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['permissioncategory_id']);
            $table->dropColumn('permissioncategory_id');
        });
    }
}
