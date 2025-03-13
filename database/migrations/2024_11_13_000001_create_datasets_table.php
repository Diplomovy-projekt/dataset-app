<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('display_name')->nullable();
            $table->string('unique_name')->unique();
            $table->text('description')->nullable();
            $table->integer('num_images');
            $table->integer('total_size');
            $table->string('annotation_technique');
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index('unique_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('datasets');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
