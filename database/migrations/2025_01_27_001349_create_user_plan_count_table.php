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
        Schema::create('user_plan_count', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_plan_id');
            $table->integer('n_audios');
            $table->integer('n_videos');
            $table->timestamps();

            $table->foreign('user_plan_id')->references('id')->on('plan_profile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_plan_count');
    }
};
