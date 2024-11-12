<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatosCalificaciones extends Model
{
    use HasFactory;

    protected $table = 'datosCalificaciones';

    public $timestamps = true;

    protected $fillable = [
        'descripcion',
        'descripcion_breve',
        'apellidos_nombre',
        'ciclo',
        'matricula',
        'evaluacion',
        'valor',
    ];
}
