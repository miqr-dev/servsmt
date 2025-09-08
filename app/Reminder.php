<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['ticket_id', 'reminder_date', 'is_reminded','user_id'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
        public function user()
    {
        return $this->belongsTo(User::class);
    }
}
