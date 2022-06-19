<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'forum_user_pivot', 'forum_id', 'user_id' );
    }

    public function chat()
    {
        return $this->hasOne(Chat::class);
    }
}
