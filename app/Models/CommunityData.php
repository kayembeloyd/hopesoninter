<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityData extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'location'
    ];

    public function community_data_media()
    {
        return $this->hasMany(CommunityDataMedia::class, 'community_data_id');
    }
}
