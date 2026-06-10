<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->index();
            $table->string('account_name')->nullable();
            $table->enum('section', ['assets', 'liabilities', 'equity']);
            $table->decimal('balance', 20, 3);
            $table->string('currency', 3)->default('LYD');
            $table->date('report_date');
            $table->timestamps();
            
            $table->index(['section', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};