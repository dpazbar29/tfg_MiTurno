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
        Schema::create('reservas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('usuario_id'); // FK usuarios (cliente)
            $table->unsignedBigInteger('empleado_id')->nullable(); // FK empleados
            $table->unsignedBigInteger('servicio_id'); // FK servicios

            $table->dateTime('fecha_hora_inicio');
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'completada', 'ausencia'])->default('pendiente');
            $table->text('notas')->nullable();

            $table->timestamps();

            // FK
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');
            $table->foreign('servicio_id')->references('id')->on('servicios')->onDelete('cascade');

            //Índice para consultas por fecha
            $table->index(['fecha_hora_inicio', 'empleado_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
