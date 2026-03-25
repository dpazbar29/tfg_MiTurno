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
        Schema::create('horarios', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('empleado_id'); // FK empleados
            $table->integer('dia_semana'); // 0-6
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('tipo', ['normal', 'festivo', 'cierre']);
            $table->boolean('activo')->default(true);

            $table->timestamps(); // created_at, updated_at

            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
