<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerFile extends Model
{
    protected $fillable = ['filename', 'original_name', 'report_date', 'total_assets', 'total_liabilities'];
    
    protected $casts = [
        'report_date' => 'date',
    ];
}