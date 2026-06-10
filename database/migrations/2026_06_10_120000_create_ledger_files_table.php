<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_name');
            $table->date('report_date')->nullable();
            $table->integer('total_assets')->default(0);
            $table->integer('total_liabilities')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_files');
    }
};