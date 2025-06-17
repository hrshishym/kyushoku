<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->text('main_dish')->nullable();
            $table->text('side_dish')->nullable();
            $table->text('soup')->nullable();
            $table->text('rice')->nullable();
            $table->text('drink')->nullable();
            $table->text('dessert')->nullable();
            $table->text('other')->nullable();
            $table->timestamps();
            
            $table->unique('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menus');
    }
};