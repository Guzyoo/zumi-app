<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot_code',
        'is_occupied',
        'last_updated_at',
    ];

    protected $casts = [
        'is_occupied' => 'boolean',
        'last_updated_at' => 'datetime',
    ];
}