<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentVariable extends Model
{
    protected $fillable = ['document_id', 'key', 'value'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}