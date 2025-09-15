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
        Schema::create('absens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('token_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('progress_file')->nullable();
            $table->string('kode')->unique()->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->dateTime('tanggal');
            $table->string('shift')->nullable();
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absens');
    }
};
