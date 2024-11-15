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
        $evaluationMapping = [
            'P1' => 'Ordinario',
            'P2' => 'Ordinario',
            'P3' => 'Ordinario',
            'PFO' => 'Ordinario',
            'EQ' => 'Equivalencia',
            'EXT1' => 'Extraordinario 1',
            'EXT2' => 'Extraordinario 2',
        ];

        foreach (['P1', 'P2', 'P3', 'PFO', 'EQ', 'EXT1', 'EXT2'] as $key) {
            if ($evaluations->contains('evaluacion', $key)) {
                return $evaluationMapping[$key];
            }
        }
        return null;
    }

    private function obtenerCicloMasReciente($datos)
    {
        $ciclos = $datos->pluck('ciclo')->unique()->filter()->sort()->values()->all();
        usort($ciclos, function ($a, $b) {
            [$yearA, $partA] = explode('-', $a);
            [$yearB, $partB] = explode('-', $b);

            if ($yearA != $yearB) {
                return $yearA <=> $yearB;
            }

            $orderCB_CA = ['CB' => 0, 'CC' => 1, 'CA' => 2];
            $orderS2_S1 = ['S2' => 0, 'S1' => 1];

            if (isset($orderCB_CA[$partA]) && isset($orderCB_CA[$partB])) {
                return $orderCB_CA[$partA] <=> $orderCB_CA[$partB];
            } elseif (isset($orderS2_S1[$partA]) && isset($orderS2_S1[$partB])) {
                return $orderS2_S1[$partA] <=> $orderS2_S1[$partB];
            } else {
                return 0;
            }
        });
        return end($ciclos);
    }

    private function procesarCalificaciones($datos, $cuatrimestres)
    {
        return $datos->groupBy(['matricula', 'descripcion'])->map(function ($materias) {
            return $materias->map(function ($evaluaciones) {
                $p_values = $evaluaciones->filter(function ($item) {
                    return in_array($item->evaluacion, ['P1', 'P2', 'P3']);
                })->groupBy('evaluacion')->map(function ($items) {
                    return [
                        'valor' => $items->max('valor'),
                        'ciclo' => $items->first()->ciclo,
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
    }

    private function calcularPromediosPorCuatrimestre($calificaciones, $cuatrimestres)
    {
        $promediosCuatrimestresPorAlumno = [];
        foreach ($calificaciones as $matricula => $materiasAlumno) {
            foreach ($cuatrimestres as $cuatrimestre => $materias) {
                $totalPromediosCuatrimestre = 0;
                $totalCalificacionesCuatrimestre = 0;
                foreach ($materias as $materia => $creditos) {
                    if (isset($materiasAlumno[$materia]['promedio_p'])) {
                        $totalPromediosCuatrimestre += $materiasAlumno[$materia]['promedio_p'];
                        $totalCalificacionesCuatrimestre++;
                    }
                }
                $promediosCuatrimestresPorAlumno[$matricula][$cuatrimestre] = $totalCalificacionesCuatrimestre > 0
                    ? round($totalPromediosCuatrimestre / $totalCalificacionesCuatrimestre, 1)
                    : null;
            }
        }
        return $promediosCuatrimestresPorAlumno;
    }

    private function calcularPromedioGeneral($promediosCuatrimestresPorAlumno)
    {
        $promedioGeneralPorAlumno = [];
        foreach ($promediosCuatrimestresPorAlumno as $matricula => $cuatrimestres) {
            $totalPromedios = array_sum(array_filter($cuatrimestres));
            $totalCuatrimestres = count(array_filter($cuatrimestres));
            $promedioGeneralPorAlumno[$matricula] = $totalCuatrimestres > 0
                ? round($totalPromedios / $totalCuatrimestres, 1)
                : null;
        }
        return $promedioGeneralPorAlumno;
    }

    private function calcularMateriasReprobadas($calificaciones, $cuatrimestres)
    {
        $materiasReprobadasPorAlumno = [];
        foreach ($calificaciones as $matricula => $materiasAlumno) {
            $reprobadas = 0;
            foreach ($cuatrimestres as $cuatrimestre => $materias) {
                foreach ($materias as $materia => $creditos) {
                    if (isset($materiasAlumno[$materia]['promedio_p']) && $materiasAlumno[$materia]['promedio_p'] < 6) {
                        $reprobadas++;
                    }
                }
            }
            $materiasReprobadasPorAlumno[$matricula] = $reprobadas;
        }
        return $materiasReprobadasPorAlumno;
    }

    public function mostrarCalificacionesAdmonNegMixta(Request $request)
    {
        $search = $request->input('search');
        $selectedCiclo = $request->input('selected_ciclo');

        $cuatrimestres = [
            'Primer Cuatrimestre' => [
                'Habilidades de Comunicación y Expresión Oral y Escrita' => 7,
                'Tecnologías de la Información y de la Comunicación' => 7,
                'Administración' => 7,
                'Derecho I (Laboral y Civil)' => 7,
                'Metodología de la Investigación' => 7,
                'Introducción Financiera' => 7,
            ],
            'Segundo Cuatrimestre' => [
                'Mercadotecnia' => 7,
                'Administración, Innovación y Modelos de Negocios' => 7,
                'Derecho II (Mercantil y Fiscal)' => 7,
                'Matemáticas Aplicadas a los Negocios' => 7,
                'Contabilidad I' => 7,
                'Microeconomía' => 7,
            ],
            'Tercer Cuatrimestre' => [
                'Comportamiento del Consumidor' => 7,
                'Probabilidad y Estadística aplicada a los negocios' => 7,
                'Contabilidad II' => 7,
                'Inteligencia Emocional' => 7,
                'Comunicación Organizacional' => 7,
                'Macroeconomía' => 7,
            ],
            'Cuarto Cuatrimestre' => [
                'Investigación de Mercados Cualitativa' => 7,
                'Diseño Gráfico' => 7,
                'Finanzas Aplicadas a la toma de Decisiones' => 7,
                'Negocios y Comercio Internacional' => 7,
                'Sistemas de Información Gerencial' => 7,
                'Publicidad y Promoción' => 7,
            ],
            'Quinto Cuatrimestre' => [
                'Investigación de Mercados Cuantitativa' => 7,
                'Competitividad Global' => 7,
                'Administración y Procesos de Ventas' => 7,
                'Finanzas Personales y Empresariales' => 7,
                'Relaciones Públicas' => 7,
                'Estrategia de Precios' => 7,
            ],
            'Sexto Cuatrimestre' => [
                'Ética, Responsabilidad Social Empresarial y Desarrollo Sostenible' => 7,
                'Inteligencia de Mercados' => 7,
                'Mercadotecnia Digital I' => 7,
                'Evaluación de Proyectos y Fuentes de Financiamiento' => 7,
                'Producción de Medios Interactivos' => 7,
                'Mercadotecnia Internacional' => 7,
            ],
            'Séptimo Cuatrimestre' => [
                'Estrategias de Distribución y Comercialización' => 7,
                'Mercadotecnia Digital II' => 7,
                'Mercadotecnia Estratégica entre Negocios' => 7,
                'Control de Presupuestos y Administración de Operaciones' => 7,
                'Mercadotecnia de Servicios' => 7,
                'Desarrollo de Marcas y Nuevos Productos' => 7,
            ],
            'Octavo Cuatrimestre' => [
                'Seminario de Comunicación Integral' => 7,
                'Seminario Integrador de Mercadotecnia Estratégica' => 7,
                'Tópicos de Especialidad I' => 7,
                'Tópicos de Especialidad II' => 7,
                'Tópico de Especialidad III' => 7,
                'Habilidades de Liderazgo' => 7,
            ],
            // Agrega los demás cuatrimestres y sus materias aquí
        ];


        $query = DatosCalificaciones::where('descripcion_breve', 'MERCADOTECNIA MIXTA');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('apellidos_nombre', 'like', '%' . $search . '%')
                    ->orWhere('matricula', 'like', '%' . $search . '%');
            });
        }

        $datos = $query->get();

        // Obtener los ciclos existentes y el ciclo más reciente
        $ciclosExistentes = $datos->pluck('ciclo')->unique()->filter()->sort()->values()->all();
        $cicloMasReciente = $this->obtenerCicloMasReciente($datos);

        // Si no se seleccionó un ciclo, usar el más reciente
        if (!$selectedCiclo) {
            $selectedCiclo = $cicloMasReciente;
        }

        $calificaciones = $this->procesarCalificaciones($datos, $cuatrimestres);
        $nombresAlumnos = $datos->pluck('apellidos_nombre', 'matricula');
        $promediosCuatrimestresPorAlumno = $this->calcularPromediosPorCuatrimestre($calificaciones, $cuatrimestres);
        $promedioGeneralPorAlumno = $this->calcularPromedioGeneral($promediosCuatrimestresPorAlumno);
        $materiasReprobadasPorAlumno = $this->calcularMateriasReprobadas($calificaciones, $cuatrimestres);

        // Calcular los créditos totales de todas las materias
        $creditosTotales = collect($cuatrimestres)->flatten()->sum();

        // Inicializar arrays para almacenar los créditos cursados y faltantes por cada alumno
        $creditosCursadosPorAlumno = [];
        $creditosFaltantesPorAlumno = [];

        foreach ($calificaciones as $matricula => $materiasAlumno) {
            $creditosCursados = 0;

            foreach ($cuatrimestres as $cuatrimestre => $materias) {
                foreach ($materias as $materia => $creditos) {
                    if (isset($materiasAlumno[$materia]) && ($materiasAlumno[$materia]['promedio_p'] ?? 0) >= 6) {
                        $creditosCursados += $creditos;
                    }
                }
            }

            $creditosCursadosPorAlumno[$matricula] = $creditosCursados;
            $creditosFaltantesPorAlumno[$matricula] = $creditosTotales - $creditosCursados;
        }

        return view('reportes/calificaciones/carreras/calificaciones_admon_neg_mixta', compact(
            'cuatrimestres',
            'calificaciones',
            'nombresAlumnos',
            'search',
            'selectedCiclo',
            'ciclosExistentes',
            'promediosCuatrimestresPorAlumno',
            'promedioGeneralPorAlumno',
            'materiasReprobadasPorAlumno',
            'cicloMasReciente',
            'creditosTotales',
            'creditosCursadosPorAlumno',
            'creditosFaltantesPorAlumno'
        ));
    }
}
