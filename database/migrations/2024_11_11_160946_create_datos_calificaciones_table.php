<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatosCalificacionesTable extends Migration
{
    public function up()
    {
        Schema::create('datosCalificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion')->nullable();
            $table->string('descripcion_breve')->nullable();
            $table->string('apellidos_nombre')->nullable();
            $table->string('ciclo')->nullable();
            $table->string('matricula')->nullable();
            $table->string('evaluacion')->nullable();
            $table->double('valor', 8, 2)->nullable(); // Permitir nulos en la columna 'valor'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('datosCalificaciones');
    }
}

