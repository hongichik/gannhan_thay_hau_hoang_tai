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
        Schema::create('intrusions', function (Blueprint $table) {
            $table->id();
            $table->string('word_1')->nullable();
            $table->string('word_2')->nullable();
            $table->string('word_3')->nullable();
            $table->string('word_4')->nullable();
            $table->string('word_5')->nullable();
            $table->string('word_6')->nullable();
            $table->unsignedBigInteger('outlier_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intrusions');
    }
};
