<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->constrained('analyses')->cascadeOnDelete();
            $table->decimal('probability', 5, 2)->nullable();
            $table->string('label');
            $table->float('image_score')->nullable();
            $table->float('tabular_score')->nullable();
            $table->float('symptoms_score')->nullable();
            $table->timestamps();
            $table->unique('analysis_id');
        });
    }
    public function down(): void { Schema::dropIfExists('results'); }
};
