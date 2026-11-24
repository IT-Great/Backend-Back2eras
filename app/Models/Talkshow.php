<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Talkshow extends Model
// {
//     use HasFactory;

//     protected $fillable = [
//         'poster',
//         'speaker_name',
//         'icon',
//         'theme',
//         'schedule',
//         'status',
//     ];

//     protected $appends = ['poster_url'];

//     public function getPosterUrlAttribute()
//     {
//         return $this->poster ? asset('storage/' . $this->poster) : null;
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Talkshow extends Model
{
    use HasFactory;

    protected $fillable = [
        'poster',
        'speaker_name',
        'icon',
        'theme',
        'schedule',
    ];

    protected $appends = ['poster_url'];

    public function getPosterUrlAttribute()
    {
        return $this->poster ? asset('storage/' . $this->poster) : null;
    }

    public function registrations()
    {
        return $this->hasMany(TalkshowRegistration::class);
    }
}
