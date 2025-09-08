<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrachenberg93sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trachenberg93s', function (Blueprint $table) {
            $table->id();
            $table->string('section')->nullable;
            $table->string('area')->nullable;
            $table->string('ip_address')->nullable;
            $table->string('name')->nullable;
            $table->string('description')->nullable;
            $table->string('username')->nullable;
            $table->string('password')->nullable;
            $table->string('notes')->nullable;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trachenberg93s');
    }
}
