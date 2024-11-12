<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatosCalificaciones;
use App\Imports\DatosCalificacionesImport;
use Maatwebsite\Excel\Facades\Excel;

class DatosCalificacionesController extends Controller
{
    // Mostrar los datos en una tabla
    public function index()
    {
        $datos = DatosCalificaciones::all();
        return view('reportes/calificaciones/datos', compact('datos'));
    }

    // Mostrar el formulario de edición
    public function edit($id)
    {
        $dato = DatosCalificaciones::findOrFail($id);
        return view('reportes/calificaciones/edit', compact('dato'));
    }

    // Actualizar un registro
    public function update(Request $request, $id)
    {
        $dato = DatosCalificaciones::findOrFail($id);
        $dato->update($request->all());

        return redirect()->route('datos.index')->with('success', 'Registro actualizado correctamente.');
    }

    // Eliminar registros seleccionados
    public function delete(Request $request)
    {
        $ids = $request->input('ids');
        DatosCalificaciones::whereIn('id', $ids)->delete();

        return redirect()->route('datos.index')->with('success', 'Registros eliminados correctamente.');
    }

    // Mostrar el formulario de importación
    public function showImportForm()
    {
        return view('reportes/calificaciones/import');
    }

    // Importar datos con opciones para sustituir o agregar
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
            'action' => 'required|in:replace,append',
        ]);

        if ($request->action == 'replace') {
            // Eliminar todos los registros antes de importar
            DatosCalificaciones::truncate();
        }

        Excel::import(new DatosCalificacionesImport, $request->file('file'));

        return redirect()->route('datos.index')->with('success', 'Datos importados correctamente.');
    }
}
