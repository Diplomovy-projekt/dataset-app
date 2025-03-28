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
        Schema::create('annotation_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->foreignId('annotation_class_id')->constrained()->onDelete('cascade');
            $table->float('x', 5);
            $table->float('y', 5);
            $table->float('width', 5);
            $table->float('height', 5);
            $table->json('segmentation')->nullable();
            $table->string('svg_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('annotation_data');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
