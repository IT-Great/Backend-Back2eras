<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kol extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'icon',
        'email',
        'whatsapp',
        'social_media_link',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
