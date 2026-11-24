<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalkshowRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'talkshow_id',
        'name',
        'email',
        'phone',
        'institution',
        'status',
    ];

    public function talkshow()
    {
        return $this->belongsTo(Talkshow::class);
    }
}
