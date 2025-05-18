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
        Schema::create('monto_creador', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('creador_id');
            $table->integer('n_descargas')->default(0);
            $table->decimal('monto_mes')->default(0);
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monto_creador');
    }
};
