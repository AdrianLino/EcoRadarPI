<?php

namespace App\Imports;

use App\Models\DatosCalificaciones;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue; // Importa esta interfaz

class DatosCalificacionesImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        return new DatosCalificaciones([
            'descripcion' => $row['descripcion'] ?? null,
            'descripcion_breve' => $row['descripcionbreve'] ?? null,
            'apellidos_nombre' => $row['apellidosnombre'] ?? null,
            'ciclo' => $row['ciclo'] ?? null,
            'matricula' => $row['matricula'] ?? null,
            'evaluacion' => $row['evaluacion'] ?? null,
            'valor' => isset($row['valor']) ? (double) $row['valor'] : null,
        ]);
    }

    public function chunkSize(): int
    {
        return 1000; // Procesa 1000 filas por vez para reducir uso de memoria
    }
}
