<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatosCalificaciones;

class MostrarCalificacionesController extends Controller
{

    private function customRound($value)
    {
        return ($value - floor($value)) >= 0.5 ? ceil($value) : floor($value);
    }


    private function getEvaluationType($evaluations)
    {
        // Mapeo de tipos de evaluación a leyendas
        $evaluationMapping = [
            'P1' => 'Ordinario',
            'P2' => 'Ordinario',
            'P3' => 'Ordinario',
            'PFO' => 'Ordinario',
            'EQ' => 'Equivalencia',
            'EXT1' => 'Extraordinario 1',
            'EXT2' => 'Extraordinario 2',
        ];

        // Buscar el tipo de evaluación más alto disponible
        foreach (['P1', 'P2', 'P3', 'PFO', 'EQ', 'EXT1', 'EXT2'] as $key) {
            if ($evaluations->contains('evaluacion', $key)) {
                return $evaluationMapping[$key];
            }
        }

        return null;
    }


    public function mostrarCalificacionesAdmonNegMixta(Request $request)
    {
        // Obtener el término de búsqueda si está presente
        $search = $request->input('search');

        // Definimos las materias organizadas por cuatrimestre
        $cuatrimestres = [
            'Primer Cuatrimestre' => [
                'Habilidades de Comunicación y Expresión Oral y Escrita',
                'Tecnologías de la Información y de la Comunicación',
                'Administración',
                'Derecho I (Laboral y Civil)',
                'Metodología de la Investigación',
                'Introducción Financiera',
            ],
            'Segundo Cuatrimestre' => [
                'Mercadotecnia',
                'Administración, Innovación y Modelos de Negocios',
                'Derecho II',
                'Matemáticas Aplicadas a los Negocios',
                'Contabilidad I',
                'Microeconomía',
            ],
            'Tercer Cuatrimestre' => [
                'Comportamiento del Consumidor',
                'Probabilidad y Estadística aplicada a los negocios',
                'Contabilidad II',
                'Inteligencia Emocional',
                'Comunicación Organizacional',
                'Macroeconomía',
            ],
            'Cuarto Cuatrimestre' => [
                'Empresa y Cultura Global',
                'Mercadotecnia Digital',
                'Finanzas Aplicadas a la toma de Decisiones',
                'Comportamiento Organizacional',
                'Negocios y Comercio Internacional',
                'Sistemas de Información Gerencial',
            ],
            'Quinto Cuatrimestre' => [
                'Competitividad Global',
                'Administración y Procesos de Ventas',
                'Análisis y Administración de la Cadena de Valor',
                'Administración de Operaciones',
                'Realidad Mexicana Contemporánea',
                'Finanzas Personales y Empresariales',
            ],
            'Sexto Cuatrimestre' => [
                'Ética, Responsabilidad Social Empresarial y Desarrollo Sostenible',
                'Evaluación de Proyectos y Fuentes de Financiamiento',
                'Administración de Calidad',
                'Desarrollo de Habilidades Directivas',
                'Estructura Organizacional de la Empresa',
                'Auditoría Administrativa',
            ],
            'Séptimo Cuatrimestre' => [
                'Cadena de Suministro',
                'Estrategias de Distribución y Comercialización',
                'Estrategias Fiscales',
                'Mercadotecnia de Servicios',
                'Consultoría Administrativa',
                'Análisis de Mercados Emergentes',
            ],
            'Octavo Cuatrimestre' => [
                'Tópicos de Especialidad I',
                'Habilidades de Liderazgo',
                'Planeación Estratégica',
                'Seminario de Dirección Estratégica',
                'Tópicos de Especialidad II',
                'Tópicos de Especialidad III',
            ],
        ];


       
        $query = DatosCalificaciones::where('descripcion_breve', 'ADMON.NEG.MIXTA');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('apellidos_nombre', 'like', '%' . $search . '%')
                  ->orWhere('matricula', 'like', '%' . $search . '%');
            });
        }

        $datos = $query->get();

        // Identificar el ciclo más reciente usando el orden CB, CC, CA
        $ciclos = $datos->pluck('ciclo')->unique()->filter()->sort()->values()->all();

        usort($ciclos, function ($a, $b) {
            // Separar el año y la parte del ciclo (CB, CC, CA)
            [$yearA, $partA] = explode('-', $a);
            [$yearB, $partB] = explode('-', $b);

            // Ordenar primero por año en orden ascendente
            if ($yearA != $yearB) {
                return $yearA <=> $yearB;
            }

            // Ordenar la parte del ciclo en el orden CB, CC, CA
            $order = ['CB' => 0, 'CC' => 1, 'CA' => 2];
            return $order[$partA] <=> $order[$partB];
        });

        // El ciclo más reciente será el último en el array después de ordenar
        $cicloMasReciente = end($ciclos);

        // Organizar las calificaciones incluyendo el ciclo
        $calificaciones = $datos->groupBy(['matricula', 'descripcion'])->map(function ($materias) {
            return $materias->map(function ($evaluaciones) {
                $p_values = $evaluaciones->filter(function ($item) {
                    return in_array($item->evaluacion, ['P1', 'P2', 'P3']);
                })->groupBy('evaluacion')->map(function ($items) {
                    return [
                        'valor' => $items->max('valor'),
                        'ciclo' => $items->first()->ciclo, // Tomamos el ciclo del primer elemento
                    ];
                });

                $promedio_p = $p_values->isNotEmpty() ? max($this->customRound($p_values->avg('valor')), 5) : null;

                $otros_values = $evaluaciones->filter(function ($item) {
                    return in_array($item->evaluacion, ['EQ', 'EXT1', 'EXT2', 'EXT3', 'PFO']);
                })->groupBy('evaluacion')->map(function ($items) {
                    return [
                        'valor' => $items->max('valor'),
                        'ciclo' => $items->first()->ciclo,
                    ];
                });

                $max_otro = $otros_values->isNotEmpty() ? max($this->customRound($otros_values->max('valor')), 5) : null;
                $evaluationType = $this->getEvaluationType($evaluaciones);

                return [
                    'promedio_p' => $promedio_p,
                    'max_otro' => $max_otro,
                    'ciclo_p' => $p_values->isNotEmpty() ? $p_values->first()['ciclo'] : null,
                    'ciclo_otro' => $otros_values->isNotEmpty() ? $otros_values->first()['ciclo'] : null,
                    'evaluation_type' => $evaluationType,
                ];
            });
        });

        $nombresAlumnos = $datos->pluck('apellidos_nombre', 'matricula');

        // Calcular promedios por cuatrimestre, promedio general y materias reprobadas para cada alumno
        $promediosCuatrimestresPorAlumno = [];
        $promedioGeneralPorAlumno = [];
        $materiasReprobadasPorAlumno = [];

        foreach ($calificaciones as $matricula => $materiasAlumno) {
            $totalPromediosAlumno = 0;
            $totalCalificacionesAlumno = 0;
            $reprobadas = 0;

            foreach ($cuatrimestres as $cuatrimestre => $materias) {
                $totalPromediosCuatrimestre = 0;
                $totalCalificacionesCuatrimestre = 0;

                foreach ($materias as $materia) {
                    if (isset($materiasAlumno[$materia]['promedio_p'])) {
                        $calificacion = $materiasAlumno[$materia]['promedio_p'];

                        // Contar como reprobada si la calificación es menor a 6
                        if ($calificacion < 6) {
                            $reprobadas++;
                        }

                        $totalPromediosCuatrimestre += $calificacion;
                        $totalPromediosAlumno += $calificacion;
                        $totalCalificacionesCuatrimestre++;
                        $totalCalificacionesAlumno++;
                    }
                }

                $promediosCuatrimestresPorAlumno[$matricula][$cuatrimestre] = $totalCalificacionesCuatrimestre > 0
                    ? round($totalPromediosCuatrimestre / $totalCalificacionesCuatrimestre, 1)
                    : null;
            }

            $promedioGeneralPorAlumno[$matricula] = $totalCalificacionesAlumno > 0
                ? round($totalPromediosAlumno / $totalCalificacionesAlumno, 1)
                : null;

            $materiasReprobadasPorAlumno[$matricula] = $reprobadas;
        }

        return view('reportes/calificaciones/carreras/calificaciones_admon_neg_mixta', compact(
            'cuatrimestres', 'calificaciones', 'nombresAlumnos', 'search', 
            'promediosCuatrimestresPorAlumno', 'promedioGeneralPorAlumno', 
            'materiasReprobadasPorAlumno', 'cicloMasReciente'
        ));
    }
}