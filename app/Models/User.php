<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'access',
        'phone_numbers',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function user_media()
    {
        return $this->hasMany(UserMedia::class);
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function requesting_membership_community()
    {
        return $this->belongsTo(Community::class, 'requesting_membership_community_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function webnar_events()
    {
        return $this->hasMany(WebnarEvent::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'owner_id');
    }

    public function forums()
    {
        return $this->belongsToMany(Forum::class, 'forum_user_pivot', 'user_id', 'forum_id');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
