<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->foreignId('ledger_file_id')->nullable()->after('id')->constrained('ledger_files')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropForeign(['ledger_file_id']);
            $table->dropColumn('ledger_file_id');
        });
    }
};