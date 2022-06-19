<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'only_community_members'
    ];

    public function attendees()
    {
        return $this->hasMany(User::class);
    }

    public function creater()
    {
        return $this->hasOne(User::class);
    }
}
