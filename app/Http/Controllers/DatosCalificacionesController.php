<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatosCalificaciones;
use App\Imports\DatosCalificacionesImport;
use Maatwebsite\Excel\Facades\Excel;

class DatosCalificacionesController extends Controller
{
    // Mostrar los datos en una tabla
    public function index(Request $request)
{
    // Verifica si hay un término de búsqueda en la columna `apellidos_nombre`
    $search = $request->input('search');

    // Si hay búsqueda, filtra los registros; si no, carga todos los registros con paginación
    $query = DatosCalificaciones::query();

    if ($search) {
        $query->where('apellidos_nombre', 'LIKE', '%' . $search . '%');
    }

    // Cargar 50 registros por página
    $datos = $query->paginate(50);

    return view('reportes/calificaciones/datos', compact('datos', 'search'));
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

    public function importFile(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        try {
            Excel::import(new DatosCalificacionesImport, $request->file('file')->getRealPath());

            return response()->json([
                'message' => 'Importación completada',
                'fileIndex' => $request->input('fileIndex'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al importar el archivo',
                'fileIndex' => $request->input('fileIndex'),
            ]);
        }
    }
}
