<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketSpecialComment extends Model
{
    protected $table = 'ticket_special_comments';
    protected $fillable = ['ticket_id','user_id','body'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
