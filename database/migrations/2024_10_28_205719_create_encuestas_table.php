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
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id(); // Llave primaria
            $table->string('periodo')->nullable(); // PERIODO
            $table->string('modalidad')->nullable(); // MODALIDAD
            $table->string('grupo')->nullable(); // GRUPO
            $table->string('direccion')->nullable(); // DIRECCIÓN
            $table->string('profesor')->nullable(); // PROFESOR
            $table->string('asignatura')->nullable(); // ASIGNATURA
            $table->double('evaluacion')->nullable(); // EVALUACIÓN
            $table->integer('encuestas')->nullable(); // ENCUESTAS

            // Columnas para los números del 1 al 20
            for ($i = 1; $i <= 20; $i++) {
                $table->double("respuesta_$i")->nullable(); // 1, 2, 3,... hasta 20
            }

            // Columnas para comentarios del 1 al 13
            for ($i = 1; $i <= 13; $i++) {
                $table->text("comentario_$i")->nullable(); // COMENTARIOS 1, COMENTARIOS 2,... hasta COMENTARIOS 13
            }

            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encuestas');
    }
};
