<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'trading_account',
        'email',
        'first_name',
        'last_name',
        'amount',
        'net_amount',
        'currency',
        'status',
        'payment_gateway',
        'payment_id',
        'last_contact',
        'offer',
        'crypto_address',
        'reference_code',
        'lead_source',
        'account_manager'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'last_contact' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
