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
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('bpm');
            $table->string('version');
            $table->string('type');
            $table->string('path');
            $table->string('path_preview');
            $table->integer('n_downloads');
            $table->boolean('active');
            $table->boolean('slider_new');
            $table->unsignedInteger('artists_id');
            $table->unsignedInteger('genres_id');
            
            $table->timestamps();

            $table->foreign('artists_id')->references('id')->on('artists');
            $table->foreign('genres_id')->references('id')->on('genres');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
