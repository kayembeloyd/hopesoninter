<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebnarEvent extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'name',
        'on_date_time',
        'is_live'
    ];

    public function webnar_event_media()
    {
        return $this->hasMany(WebnarEventMedia::class, 'webnar_event_id');
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }
}
