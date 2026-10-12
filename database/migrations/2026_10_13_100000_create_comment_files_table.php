<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pictures and attachments of ticket comments (IT, Handwerk, Korso), 2026-10-13.
 *
 * A file is uploaded while the comment is being written (comment_id NULL,
 * only the uploader may open it) and linked when the comment is saved.
 * Unlinked uploads are removed after 3 days (servsmt:comment-files-cleanup).
 * The files themselves live in storage/app/comment-files (not public; served
 * by CommentFileController after the same access check as the ticket).
 * Additive only.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comment_files')) {
            return;
        }
        Schema::create('comment_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_id')->nullable()->index();
            $table->string('commentable_type');
            $table->unsignedBigInteger('commentable_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('disk_path');
            $table->string('original_name');
            $table->string('mime', 191);
            $table->unsignedBigInteger('size')->default(0);
            $table->boolean('inline')->default(false); // picture inside the text
            $table->timestamps();
            $table->index(['commentable_type', 'commentable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_files');
    }
};
