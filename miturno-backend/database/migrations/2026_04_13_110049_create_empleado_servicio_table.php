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
        Schema::create('empleado_servicio', function (Blueprint $table) {
            $table->unsignedBigInteger('empleado_id');
            $table->unsignedBigInteger('servicio_id');

            $table->foreign('empleado_id')
                ->references('id')
                ->on('empleados')
                ->onDelete('cascade');

            $table->foreign('servicio_id')
                ->references('id')
                ->on('servicios')
                ->onDelete('cascade');

            $table->primary(['empleado_id', 'servicio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado_servicio');
    }
};
