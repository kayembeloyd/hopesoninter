<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'content',
        'to_uid',
        'from_uid',
        'to_forum_id',
        'status'
    ];

    public function to_forum()
    {
        return $this->belongsTo(Forum::class, 'to_forum_id');
    }

    public function to_user()
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    public function from_user()
    {
        return $this->belongsTo(User::class, 'from_uid');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
