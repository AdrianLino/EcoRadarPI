<?php

namespace App\Imports;

use App\Models\Encuesta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EncuestaImport implements ToCollection
{
    /**
     * Recibe la colección de filas del archivo Excel.
     *
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // Asume que la primera fila son los encabezados, por lo que la saltamos.
        foreach ($collection as $index => $row) {
            if ($index === 0) {
                continue; // Saltar la primera fila con los encabezados
            }

            // Inserta los datos en la tabla 'encuestas'
            Encuesta::create([
                'periodo'     => $row[0],  // PERIODO
                'modalidad'   => $row[1],  // MODALIDAD
                'grupo'       => $row[2],  // GRUPO
                'direccion'   => $row[3],  // DIRECCIÓN
                'profesor'    => $row[4],  // PROFESOR
                'asignatura'  => $row[5],  // ASIGNATURA
                'evaluacion'  => $row[6],  // EVALUACIÓN
                'encuestas'   => $row[7],  // ENCUESTAS

                // Respuestas 1 a 20
                'respuesta_1'  => $row[8],
                'respuesta_2'  => $row[9],
                'respuesta_3'  => $row[10],
                'respuesta_4'  => $row[11],
                'respuesta_5'  => $row[12],
                'respuesta_6'  => $row[13],
                'respuesta_7'  => $row[14],
                'respuesta_8'  => $row[15],
                'respuesta_9'  => $row[16],
                'respuesta_10' => $row[17],
                'respuesta_11' => $row[18],
                'respuesta_12' => $row[19],
                'respuesta_13' => $row[20],
                'respuesta_14' => $row[21],
                'respuesta_15' => $row[22],
                'respuesta_16' => $row[23],
                'respuesta_17' => $row[24],
                'respuesta_18' => $row[25],
                'respuesta_19' => $row[26],
                'respuesta_20' => $row[27],

                // Comentarios 1 a 13
                'comentario_1'  => $row[28],
                'comentario_2'  => $row[29],
                'comentario_3'  => $row[30],
                'comentario_4'  => $row[31],
                'comentario_5'  => $row[32],
                'comentario_6'  => $row[33],
                'comentario_7'  => $row[34],
                'comentario_8'  => $row[35],
                'comentario_9'  => $row[36],
                'comentario_10' => $row[37],
                'comentario_11' => $row[38],
                'comentario_12' => $row[39],
                'comentario_13' => $row[40],
            ]);
        }
    }
}
