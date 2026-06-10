<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_files', function (Blueprint $table) {
            $table->decimal('total_assets', 20, 3)->default(0)->change();
            $table->decimal('total_liabilities', 20, 3)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ledger_files', function (Blueprint $table) {
            $table->integer('total_assets')->default(0)->change();
            $table->integer('total_liabilities')->default(0)->change();
        });
    }
};