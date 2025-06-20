<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monthly_pdfs', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dropUnique(['year', 'month']);
            $table->unique(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_pdfs', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'year', 'month']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->unique(['year', 'month']);
        });
    }
};
