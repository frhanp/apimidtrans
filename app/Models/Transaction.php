<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];

        // Izinkan semua kolom untuk diisi secara massal    protected $guarded = [];

    // Casting untuk memastikan customer_details adalah array/object
    protected $casts = [
        'customer_details' => 'array',
    ];
}
