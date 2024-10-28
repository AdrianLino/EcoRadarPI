<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    use HasFactory;

    /**
     * Los campos que pueden ser asignados de forma masiva.
     *
     * @var array
     */
    protected $fillable = [
        'periodo',        // PERIODO
        'modalidad',      // MODALIDAD
        'grupo',          // GRUPO
        'direccion',      // DIRECCIÓN
        'profesor',       // PROFESOR
        'asignatura',     // ASIGNATURA
        'evaluacion',     // EVALUACIÓN
        'encuestas',      // ENCUESTAS
        'respuesta_1',    // Respuesta 1
        'respuesta_2',    // Respuesta 2
        'respuesta_3',    // Respuesta 3
        'respuesta_4',    // Respuesta 4
        'respuesta_5',    // Respuesta 5
        'respuesta_6',    // Respuesta 6
        'respuesta_7',    // Respuesta 7
        'respuesta_8',    // Respuesta 8
        'respuesta_9',    // Respuesta 9
        'respuesta_10',   // Respuesta 10
        'respuesta_11',   // Respuesta 11
        'respuesta_12',   // Respuesta 12
        'respuesta_13',   // Respuesta 13
        'respuesta_14',   // Respuesta 14
        'respuesta_15',   // Respuesta 15
        'respuesta_16',   // Respuesta 16
        'respuesta_17',   // Respuesta 17
        'respuesta_18',   // Respuesta 18
        'respuesta_19',   // Respuesta 19
        'respuesta_20',   // Respuesta 20
        'comentario_1',   // COMENTARIOS 1
        'comentario_2',   // COMENTARIOS 2
        'comentario_3',   // COMENTARIOS 3
        'comentario_4',   // COMENTARIOS 4
        'comentario_5',   // COMENTARIOS 5
        'comentario_6',   // COMENTARIOS 6
        'comentario_7',   // COMENTARIOS 7
        'comentario_8',   // COMENTARIOS 8
        'comentario_9',   // COMENTARIOS 9
        'comentario_10',  // COMENTARIOS 10
        'comentario_11',  // COMENTARIOS 11
        'comentario_12',  // COMENTARIOS 12
        'comentario_13'   // COMENTARIOS 13
    ];
}
