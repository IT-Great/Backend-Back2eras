<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class Visitor extends Model
// {
//     protected $fillable = [
//         'full_name',
//         'email',
//         'whatsapp',
//         'instagram',
//         'subscribe_newsletter'
//     ];
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'whatsapp',
        'instagram',
        'subscribe_newsletter',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
