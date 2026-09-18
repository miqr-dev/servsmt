<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $table = 'comments';

    // Eager-load the commenter by default, matching the original
    // laravelista/comments package's Comment model - _comment.blade.php
    // reads $comment->commenter->name/->username/->vorname directly, so
    // without this every comment list would N+1 query the commenter.
    protected $with = ['commenter'];

    protected $casts = [
        'approved' => 'boolean',
    ];

    protected $fillable = [
        'comment',
        'approved',
        'guest_name',
        'guest_email',
    ];

    public function commenter()
    {
        return $this->morphTo();
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function children()
    {
        return $this->hasMany(self::class, 'child_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'child_id');
    }
}
