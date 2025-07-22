<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'customer_details' => 'array',
    ];
    protected $table = 'transactions';
}
