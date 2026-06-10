<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerAccount extends Model
{
    protected $fillable = [
        'account_code',
        'account_name',
        'section',
        'balance',
        'currency',
        'report_date',
    ];
    
    protected $casts = [
        'balance' => 'decimal:3',
        'report_date' => 'date',
    ];
}