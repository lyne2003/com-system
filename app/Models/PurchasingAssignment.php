<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PurchasingAssignment extends Model
{
    use HasUuids;

    protected $fillable = [
        'item_id',
        'assigned_supplier',
        'assigned_supplier2',
        'assigned_supplier3',
        'is_processed',
        'processed_at',
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
    ];
}
