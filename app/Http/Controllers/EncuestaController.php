<?php

namespace App\Http\Controllers;

use App\Imports\EncuestaImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Encuesta;

class EncuestaController extends Controller
{
    public function index()
    {
        return view('reportes/encuesta');
    }

    public function importar(Request $request)
    {
    // Valida que el archivo sea un Excel
    $request->validate([
        'archivo' => 'required|mimes:xlsx,xls',
    ]);

    // Importa los datos y captura la colección de datos
    $datos = Excel::toCollection(new EncuestaImport, $request->file('archivo'));

    // Muestra los datos importados en una vista
    return view('reportes/mostrar_datos', compact('datos'));
    }

    public function mostrarEncuestas()
    {
    $encuestas = Encuesta::all(); // Obtener todas las encuestas
    return view('reportes/mostrar_encuestas', compact('encuestas'));
    }


    
}
