<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location'
    ];

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function community_media()
    {
        return $this->hasMany(CommunityMedia::class);
    }

    public function community_data()
    {
        return $this->hasMany(CommunityData::class);
    }

    public function important_contacts()
    {
        return $this->hasMany(ImportantContact::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function membership_requestees()
    {
        return $this->hasMany(User::class, 'requesting_membership_community_id');
    }

    public function webnar_events()
    {
        return $this->hasMany(WebnarEvent::class);
    }
}
