<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContextToKorsoAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::table('korso_attachments', function (Blueprint $table) {
            $table->string('context')->nullable()->after('file_type');
        });
    }

    public function down()
    {
        Schema::table('korso_attachments', function (Blueprint $table) {
            $table->dropColumn('context');
        });
    }
}
