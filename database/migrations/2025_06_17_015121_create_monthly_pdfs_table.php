<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('monthly_pdfs', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->string('pdf_path');
            $table->string('original_filename');
            $table->integer('total_days_parsed')->default(0);
            $table->json('parsing_status')->nullable(); // 各日の解析状況
            $table->text('parsing_errors')->nullable();
            $table->timestamps();
            
            $table->unique(['year', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_pdfs');
    }
};