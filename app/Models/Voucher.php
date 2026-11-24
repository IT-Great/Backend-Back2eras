<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Voucher extends Model
// {
//     use HasFactory;

//     protected $fillable = [
//         'user_id',
//         'brand_slug',
//         'brand_name',
//         'code',
//         'is_used',
//         'used_at',
//     ];

//     protected function casts(): array
//     {
//         return [
//             'is_used' => 'boolean',
//             'used_at' => 'datetime',
//         ];
//     }

//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_slug',
        'brand_name',
        'code',
        'is_used',
        'used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_used'    => 'boolean',
            'used_at'    => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
