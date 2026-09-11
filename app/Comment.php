<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $table = 'comments';

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
