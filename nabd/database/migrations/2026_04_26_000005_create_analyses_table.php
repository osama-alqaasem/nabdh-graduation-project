<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('age');
            $table->decimal('troponin', 10, 4)->nullable();
            $table->decimal('ckmb', 10, 4)->nullable();
            $table->string('ecg_image_path')->nullable();
            $table->decimal('result_score', 5, 2)->nullable();
            $table->string('result_text')->nullable();
            $table->longText('raw_result')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('analyses'); }
};
