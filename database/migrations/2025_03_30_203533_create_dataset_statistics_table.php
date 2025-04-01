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
    public function up()
    {
        Schema::create('dataset_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('annotation_type'); // 'Polygon' or 'BoundingBox'
            $table->integer('dataset_count')->default(0);
            $table->integer('image_count')->default(0);
            $table->integer('annotation_count')->default(0);
            $table->integer('class_count')->default(0);
            $table->timestamp('last_updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('annotation_type');
        });
    }

    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('dataset_type_statistics');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
