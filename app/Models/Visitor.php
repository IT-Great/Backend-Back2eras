<?php

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
        'community_code',
        'subscribe_newsletter',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
