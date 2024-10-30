<?php

namespace App\Http\Controllers;

use App\Imports\EncuestaImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Encuesta;
use Illuminate\Support\Facades\DB;


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


        // Importa los datos utilizando la clase EncuestaImport
        Excel::import(new EncuestaImport, $request->file('archivo'));

        return redirect()->back()->with('success', 'Datos importados exitosamente.');
    }

    public function mostrarEncuestas()
    {
        $encuestas = Encuesta::all(); // Obtener todas las encuestas
        return view('reportes/mostrar_encuestas', compact('encuestas'));
    }



    public function listarProfesores(Request $request)
    {
        // Obtener la lista única de profesores
        $query = Encuesta::query();

        // Si hay una búsqueda, filtrarla
        if ($request->has('search')) {
            $query->where('profesor', 'like', '%' . $request->search . '%');
        }

        $profesores = $query->distinct('profesor')->pluck('profesor');

        return view('reportes.listar_profesores', compact('profesores'));
    }

    // Método para mostrar los detalles de un profesor seleccionado
    public function mostrarDetallesProfesor($profesor, Request $request)
    {
        // Array con las preguntas correspondientes a cada respuesta
        $preguntas = [
            "1. El Docente entregó y revisó el documento Planeación Didáctica y Lineamientos del Curso.",
            "2. Da seguimiento a la Planeación Didáctica que presentó.",
            "3. Identificas que prepara sus clases.",
            "4. El Docente se conduce con respeto.",
            "5. Respeta los acuerdos y lineamientos propuestos para el curso.",
            "6. Propicia la participación en clase.",
            "7. El maestro es puntual al iniciar y terminar su clase.",
            "8. Da instrucciones claras sobre las actividades a realizar.",
            "9. Desarrolla los temas de manera clara y ordenada.",
            "10. Orienta a los alumnos para buscar información adicional a la revisada en clase.",
            "11. Concede espacios de participación y respeta puntos de vista.",
            "12. El trabajo y la evaluación se centran en los temas de la asignatura.",
            "13. Muestra un manejo adecuado de las tecnologías de la información.",
            "14. Utiliza y comparte recursos tecnológicos.",
            "15. Califica las evidencias y actividades de acuerdo a la Planeación Didáctica.",
            "16. Da retroalimentación y asesorías.",
            "17. El docente muestra una actitud positiva hacia la universidad.",
            "18. Promueve y apoya las actividades generales de la universidad.",
            "19. ¿Qué tan satisfecho te sientes con el docente?"
        ];

        // Obtener los registros relacionados con el profesor
        $registros = Encuesta::where('profesor', $profesor)->get();

        // Definir el orden personalizado de las simbologías
        $ordenSimbologia = ['CB', 'CC', 'CA', 'S2', 'S1'];

        // Obtener los períodos únicos
        $periodos = $registros->pluck('periodo')->unique();

        // Determinar el período seleccionado por el usuario (por defecto es "todos")
        $periodoSeleccionado = $request->input('periodo', 'todos');

        // Filtrar los registros si se seleccionó un período específico
        if ($periodoSeleccionado !== 'todos') {
            $registros = $registros->where('periodo', $periodoSeleccionado);
        }

        // Ordenar los registros según el periodo (año y simbología)
        $registros = $registros->sort(function ($a, $b) use ($ordenSimbologia) {
            [$aYear, $aSymbol] = explode('-', $a->periodo);
            [$bYear, $bSymbol] = explode('-', $b->periodo);

            if ($aYear === $bYear) {
                return array_search($aSymbol, $ordenSimbologia) <=> array_search($bSymbol, $ordenSimbologia);
            }
            return $aYear <=> $bYear;
        });

        // Calcular el promedio de evaluaciones agrupadas por período y ordenar
        $promediosPorPeriodo = $registros->groupBy('periodo')->map(function ($items) {
            $evaluaciones = $items->pluck('evaluacion')->filter(function ($value) {
                return is_numeric($value);
            });
            return $evaluaciones->avg();  // Calcular y devolver el promedio
        })->sortKeysUsing(function ($a, $b) use ($ordenSimbologia) {
            [$aYear, $aSymbol] = explode('-', $a);
            [$bYear, $bSymbol] = explode('-', $b);

            if ($aYear === $bYear) {
                return array_search($aSymbol, $ordenSimbologia) <=> array_search($bSymbol, $ordenSimbologia);
            }
            return $aYear <=> $bYear;
        });

        // Calcular el promedio de cada respuesta (del 1 al 19) para el periodo seleccionado
        $promediosRespuestas = [];
        for ($i = 1; $i <= 19; $i++) {
            $promediosRespuestas["respuesta_$i"] = $registros->avg("respuesta_$i");
        }

        // Obtener las 5 mejores y 5 peores respuestas
        $mejoresRespuestas = collect($promediosRespuestas)->sortDesc()->take(5);
        $peoresRespuestas = collect($promediosRespuestas)->sort()->take(5);

        // Pasar los datos a la vista, incluyendo el array de preguntas
        return view('reportes.detalles_profesor', compact(
            'profesor',
            'registros',
            'periodos',
            'promediosPorPeriodo',
            'promediosRespuestas',
            'periodoSeleccionado',
            'mejoresRespuestas',
            'peoresRespuestas',
            'preguntas'
        ));
    }



    public function panelGeneral()
    {
        // Obtener todas las carreras (dirección) sin duplicados
        $carreras = Encuesta::select('direccion')->distinct()->get();

        // Calcular el promedio general de todas las evaluaciones
        $promedioGeneral = Encuesta::avg('evaluacion');

        // Calcular el promedio de evaluación por cada carrera
        $promediosPorCarrera = Encuesta::select('direccion', DB::raw('AVG(evaluacion) as promedio'))
            ->groupBy('direccion')
            ->get();

        // Pasar los datos a la vista
        return view('secciones/panel_general', compact('carreras', 'promedioGeneral', 'promediosPorCarrera'));
    }


    public function detallesCarrera($carrera, Request $request)
    {
        // Obtener todos los periodos y modalidades únicas
        $periodos = Encuesta::select('periodo')->distinct()->get();
        $modalidades = ['Ejecutiva', 'Escolarizada'];

        // Obtener la selección del usuario (periodo y modalidad)
        $periodoSeleccionado = $request->input('periodo', 'todos');
        $modalidadSeleccionada = $request->input('modalidad', 'todas');

        // Filtrar por carrera, periodo y modalidad
        $registros = Encuesta::where('direccion', $carrera);
        if ($periodoSeleccionado !== 'todos') {
            $registros->where('periodo', $periodoSeleccionado);
        }
        if ($modalidadSeleccionada !== 'todas') {
            $registros->where('modalidad', $modalidadSeleccionada);
        }
        $registros = $registros->get();

        // Calcular el promedio general de la carrera
        $promedioEvaluacion = $registros->avg('evaluacion');

        // Obtener los 10 mejores y 10 peores profesores
        $mejoresProfesores = $registros->groupBy('profesor')
            ->map(function ($items) {
                return $items->avg('evaluacion');
            })->sortDesc()->take(10);

        $peoresProfesores = $registros->groupBy('profesor')
            ->map(function ($items) {
                return $items->avg('evaluacion');
            })->sort()->take(10);

        // Obtener el promedio de cada materia
        $evaluacionesMaterias = $registros->groupBy('asignatura')
            ->map(function ($items) {
                return $items->avg('evaluacion');
            });

        // Calcular los promedios de las respuestas 1 a 19
        $respuestasPromedios = [];
        for ($i = 1; $i <= 19; $i++) {
            $respuestasPromedios["respuesta_$i"] = $registros->avg("respuesta_$i");
        }

        // Filtrar las 5 mejores y peores respuestas
        $mejoresRespuestas = collect($respuestasPromedios)->sortDesc()->take(5);
        $peoresRespuestas = collect($respuestasPromedios)->sort()->take(5);

        // Orden personalizado de simbologías
        $ordenSimbologia = ['CB', 'CC', 'CA', 'S2', 'S1'];

        // Promedio de evaluaciones por periodo (ordenado por año y simbología)
        $promediosPorPeriodo = $registros->groupBy('periodo')->map(function ($items) {
            return $items->avg('evaluacion');
        })->sortKeysUsing(function ($a, $b) use ($ordenSimbologia) {
            [$aYear, $aSymbol] = explode('-', $a);
            [$bYear, $bSymbol] = explode('-', $b);

            if ($aYear === $bYear) {
                return array_search($aSymbol, $ordenSimbologia) <=> array_search($bSymbol, $ordenSimbologia);
            }
            return $aYear <=> $bYear;
        });

        // Array de preguntas según su número
        $preguntas = [
            "1. El Docente entregó y revisó el documento Planeación Didáctica y Lineamientos del Curso.",
            "2. Da seguimiento a la Planeación Didáctica que presentó.",
            "3. Identificas que prepara sus clases.",
            "4. El Docente se conduce con respeto.",
            "5. Respeta los acuerdos y lineamientos propuestos para el curso.",
            "6. Propicia la participación en clase.",
            "7. El maestro es puntual al iniciar y terminar su clase.",
            "8. Da instrucciones claras sobre las actividades a realizar.",
            "9. Desarrolla los temas de manera clara y ordenada.",
            "10. Orienta a los alumnos para buscar información adicional a la revisada en clase.",
            "11. Concede espacios de participación y respeta puntos de vista.",
            "12. El trabajo y la evaluación se centran en los temas de la asignatura.",
            "13. Muestra un manejo adecuado de las tecnologías de la información.",
            "14. Utiliza y comparte recursos tecnológicos.",
            "15. Califica las evidencias y actividades de acuerdo a la Planeación Didáctica.",
            "16. Da retroalimentación y asesorías.",
            "17. El docente muestra una actitud positiva hacia la universidad.",
            "18. Promueve y apoya las actividades generales de la universidad.",
            "19. ¿Qué tan satisfecho te sientes con el docente?"
        ];

        // Pasar los datos a la vista
        return view('secciones/detalles_carrera', compact(
            'carrera',
            'registros',
            'promedioEvaluacion',
            'mejoresProfesores',
            'peoresProfesores',
            'evaluacionesMaterias',
            'promediosPorPeriodo',
            'periodos',
            'modalidades',
            'periodoSeleccionado',
            'modalidadSeleccionada',
            'mejoresRespuestas',
            'peoresRespuestas',
            'preguntas'
        ));
    }
}
