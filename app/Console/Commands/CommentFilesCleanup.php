<?php

namespace App\Console\Commands;

use App\CommentFile;
use Illuminate\Console\Command;

/**
 * Removes comment uploads that never became part of a saved comment (picture
 * pasted, then the comment was not sent) - older than 3 days (a draft
 * left open over the weekend keeps its pictures). Scheduled daily
 * (app/Console/Kernel.php), 2026-10-13.
 */
class CommentFilesCleanup extends Command
{
    protected $signature = 'servsmt:comment-files-cleanup {--hours=72 : nur Uploads, die älter sind}';

    protected $description = 'Kommentar-Uploads löschen, die nie in einem gespeicherten Kommentar gelandet sind';

    public function handle(): int
    {
        $count = 0;
        CommentFile::whereNull('comment_id')
            ->where('created_at', '<', now()->subHours(max(1, (int) $this->option('hours'))))
            ->orderBy('id')
            ->chunkById(200, function ($files) use (&$count) {
                foreach ($files as $file) {
                    $file->deleteWithFile();
                    $count++;
                }
            });
        $this->info("Gelöscht: {$count} nicht verwendete Kommentar-Uploads.");

        return self::SUCCESS;
    }
}
