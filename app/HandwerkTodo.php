<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HandwerkTodo extends Model
{
use SoftDeletes;

    protected $fillable = ['ticket_id', 'standort', 'title', 'body'];

    public function handwerk()
    {
        return $this->belongsTo(Handwerk::class, 'ticket_id');
    }
    public function submitter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
