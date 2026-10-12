<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Picture or attachment of a ticket comment (2026-10-13), see
 * CommentFileController and the comment_files migration.
 */
class CommentFile extends Model
{
    public const DISK = 'local';

    /** shown inside the text / in the browser; everything else is downloaded */
    public const IMAGE_MIMES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

    protected $fillable = [
        'comment_id', 'commentable_type', 'commentable_id', 'user_id',
        'disk_path', 'original_name', 'mime', 'size', 'inline',
    ];

    protected $casts = [
        'comment_id' => 'integer',
        'user_id' => 'integer',
        'size' => 'integer',
        'inline' => 'boolean',
    ];

    protected $hidden = ['disk_path', 'commentable_type', 'commentable_id', 'user_id', 'updated_at'];

    protected $appends = ['url', 'is_image'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    /** download / open link (the name only makes the URL readable) */
    public function getUrlAttribute(): string
    {
        return '/comment-files/'.$this->id.'/'.rawurlencode($this->original_name);
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime, self::IMAGE_MIMES, true);
    }

    /** removes the row and the stored file */
    public function deleteWithFile(): void
    {
        Storage::disk(self::DISK)->delete($this->disk_path);
        $this->delete();
    }
}
