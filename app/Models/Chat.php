<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'owner_id',
        'chat_with_id',
        'forum_id',
        'last_message_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function chat_with_user()
    {
        return $this->belongsTo(User::class, 'chat_with_id');
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class, 'forum_id');
    }

    public function last_message()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }
}
