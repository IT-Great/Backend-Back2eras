<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class TalkshowRegistration extends Model
// {
//     use HasFactory;

//     protected $fillable = [
//         'talkshow_id',
//         'name',
//         'email',
//         'phone',
//         'institution',
//         'status',
//     ];

//     public function talkshow()
//     {
//         return $this->belongsTo(Talkshow::class);
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalkshowRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'talkshow_id',
        'user_id',
        'name',
        'email',
        'phone',
        'institution',
        'status',
        'approved_rank',
    ];

    public function talkshow()
    {
        return $this->belongsTo(Talkshow::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
