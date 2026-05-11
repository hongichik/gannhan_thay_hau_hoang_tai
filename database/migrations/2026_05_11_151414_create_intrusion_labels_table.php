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
        Schema::create('intrusion_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('intrusion_id')->constrained('intrusions')->cascadeOnDelete();
            $table->unsignedBigInteger('outlier_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'intrusion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intrusion_labels');
    }
};
