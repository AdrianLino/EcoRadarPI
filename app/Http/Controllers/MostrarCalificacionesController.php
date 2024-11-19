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

    public function mostrarCalificacionesPorCarrera(Request $request, $carrera)
    {
        $search = $request->input('search');
        $selectedCiclo = $request->input('selected_ciclo');

        $carreras = [
           'ADMON.NEG.MIXTA' => [
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
                    'Derecho II' => 7,
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
                    'Empresa y Cultura Global' => 7,
                    'Mercadotecnia Digital' => 7,
                    'Finanzas Aplicadas a la toma de Decisiones' => 7,
                    'Comportamiento Organizacional' => 7,
                    'Negocios y Comercio Internacional' => 7,
                    'Sistemas de Información Gerencial' => 7,
                ],
                'Quinto Cuatrimestre' => [
                    'Competitividad Global' => 7,
                    'Administración y Procesos de Ventas' => 7,
                    'Análisis y Administración de la Cadena de Valor' => 7,
                    'Administración de Operaciones' => 7,
                    'Realidad Mexicana Contemporánea' => 7,
                    'Finanzas Personales y Empresariales' => 7,
                ],
                'Sexto Cuatrimestre' => [
                    'Ética, Responsabilidad Social Empresarial y Desarrollo Sostenible' => 7,
                    'Evaluación de Proyectos y Fuentes de Financiamiento' => 7,
                    'Administración de Calidad' => 7,
                    'Desarrollo de Habilidades Directivas' => 7,
                    'Estructura Organizacional de la Empresa' => 7,
                    'Auditoría Administrativa' => 7,
                ],
                'Séptimo Cuatrimestre' => [
                    'Cadena de Suministro' => 7,
                    'Estrategias de Distribución y Comercialización' => 7,
                    'Estrategias Fiscales' => 7,
                    'Mercadotecnia de Servicios' => 7,
                    'Consultoría Administrativa' => 7,
                    'Análisis de Mercados Emergentes' => 7,
                ],
                'Octavo Cuatrimestre' => [
                    'Tópicos de Especialidad I' => 7,
                    'Habilidades de Liderazgo' => 7,
                    'Planeación Estratégica' => 7,
                    'Seminario de Dirección Estratégica' => 7,
                    'Tópicos de Especialidad II' => 7,
                    'Tópicos de Especialidad III' => 7,
                ],
            ],
            'MERCADOTECNIA MIXTA' => [
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
            ],
            'DERECHO MIXTA' => [
                'Primer Cuatrimestre' => [
                    'Habilidades de Comunicación y Expresión Oral y Escrita' => 7,
                    'Introducción al Estudio del Derecho' => 7,
                    'Derecho Romano' => 7,
                    'Teoría General del Estado' => 7,
                    'Metodología de la Investigación' => 7,
                    'Tecnologías de la Información y de la Comunicación' => 7,
                ],
                'Segundo Cuatrimestre' => [
                    'Derecho Administrativo I Actos Administrativos' => 7,
                    'Teoría de la Constitución y Derecho Constitucional' => 7,
                    'Derecho Mercantil I Actos de Comercio' => 7,
                    'Derecho Civil I Personas y Familia' => 7,
                    'Derecho Penal I Teoría del Delito y de la Ley Penal' => 7,
                    'Teoría General del Proceso' => 7,
                ],
                'Tercer Cuatrimestre' => [
                    'Derecho Mercantil II Titulos y Operaciones de Crédito' => 7,
                    'Derecho Civil II Bienes y Sucesiones' => 7,
                    'Derecho Penal II Delitos en particular' => 7,
                    'Teoría de los Derechos Humanos y Derechos Fundamentales' => 7,
                    'Derecho Administrativo II Administración Pública y el Municipio' => 7,
                    'Inteligencia Emocional' => 7,
                ],
                'Cuarto Cuatrimestre' => [
                    'Derecho Mercantil III Sociedades Mercantiles' => 7,
                    'Derecho Civil III Obligaciones' => 7,
                    'Derecho Penal Acusatorio y Oral' => 7,
                    'Derecho Procesal Administrativo' => 7,
                    'Derecho Internacional' => 7,
                    'Prácticas de Derecho Civil, Familiar y Mercantil' => 7,
                ],
                'Quinto Cuatrimestre' => [
                    'Finanzas Personales y Empresariales' => 7,
                    'Derecho Procesal Mercantil' => 7,
                    'Derecho Civil IV Contratos Civiles' => 7,
                    'Ejecución de penas y medidas de seguridad' => 7,
                    'Derecho Financiero' => 7,
                    'Derecho Fiscal' => 7,
                ],
                'Sexto Cuatrimestre' => [
                    'Prácticas de Juicios Orales' => 7,
                    'Juicio de Amparo' => 7,
                    'Derecho Laboral I Derecho Social y Relaciones Interiores de Trabajo' => 7,
                    'Derecho Procesal Civil' => 7,
                    'Derecho Corporativo' => 7,
                    'Derecho Procesal Fiscal' => 7,
                ],
                'Séptimo Cuatrimestre' => [
                    'Prácticas de Amparo' => 7,
                    'Derecho Laboral II Derecho Colectivo del Trabajo' => 7,
                    'Derecho Procesal Familiar' => 7,
                    'Derecho Notarial' => 7,
                    'Derecho de la Seguridad Social' => 7,
                    'Derecho Bancario y Bursátil' => 7,
                ],
                'Octavo Cuatrimestre' => [
                    'Derecho Agrario' => 7,
                    'Derecho Registral' => 7,
                    'Derecho Procesal Laboral' => 7,
                    'Interpretación Jurídica' => 7,
                    'Justicia Alternativa' => 7,
                    'Argumentación Jurídica' => 7,
                ],
            ],
            'ADMON. NEG. ESCOLARI' => [
                'Primer Cuatrimestre' => [
                    'Habilidades de comunicación y expresión creativa' => 4,
                    'Tecnologías de la Información y de la Comunicación' => 7,
                    'Administración' => 7,
                    'Derecho I' => 7,
                    'Metodología de la Investigación' => 7,
                    'Introducción Financiera' => 7,
                    'Inglés I' => 4,
                    'Cultura y Deportes I' => 4,
                ],
                'Segundo Cuatrimestre' => [
                    'Habilidades de comunicación en el ámbito profesional' => 4,
                    'Mercadotecnia' => 7,
                    'Administración, Innovación y Modelos de Negocios' => 7,
                    'Derecho II (Mercantil y Fiscal)' => 7,
                    'Matemáticas Aplicadas a los Negocios' => 7,
                    'Contabilidad I' => 7,
                    'Inglés II' => 4,
                    'Cultura y Deportes II' => 4,
                ],
                'Tercer Cuatrimestre' => [
                    'Ética En Los Negocios' => 4,
                    'Comportamiento del Consumidor' => 7,
                    'Responsabilidad Social Empresarial y Desarrollo Sostenible' => 7,
                    'Microeconomía' => 7,
                    'Probabilidad y Estadística aplicada a los negocios' => 7,
                    'Contabilidad II' => 7,
                    'Inglés III' => 4,
                    'Cultura y Deportes III' => 4,
                ],
                'Cuarto Cuatrimestre' => [
                    'Desarrollo Humano e inteligencia emocional' => 4,
                    'Empresa y Cultura Global' => 6,
                    'Comunicación Organizacional' => 7,
                    'Macroeconomía' => 7,
                    'Innovación Tecnológica y de Mercados' => 7,
                    'Finanzas Aplicadas a la toma de Decisiones' => 7,
                    'Inglés IV' => 4,
                ],
                'Quinto Cuatrimestre' => [
                    'Habilidades de Liderazgo' => 4,
                    'Comportamiento Organizacional y Capital Humano' => 6,
                    'Competitividad Global' => 7,
                    'Negociación y Comercio Internacional' => 7,
                    'Administración y Procesos de Ventas' => 7,
                    'Inglés V' => 4,
                ],
                'Sexto Cuatrimestre' => [
                    'Emprendimiento I' => 4,
                    'Análisis y Administración de la Cadena de Valor' => 6,
                    'Sistemas de Información Gerencial' => 7,
                    'Administración de Operaciones' => 8,
                    'Evaluación de Proyectos y Fuentes de Financiamiento' => 7,
                    'Inglés VI' => 4,
                    'Residencia Profesional' => 17,
                ],
                'Séptimo Cuatrimestre' => [
                    'Emprendimiento II' => 4,
                    'Cadena de Suministro' => 5,
                    'Administración de Calidad' => 8,
                    'Realidad Mexicana Contemporánea' => 7,
                    'Estrategias de Distribución y Comercialización' => 7,
                ],
                'Octavo Cuatrimestre' => [
                    'Finanzas Personales y Empresariales' => 4,
                    'Desarrollo de Habilidades Directivas' => 8,
                    'Estrategias Fiscales' => 6,
                    'Estructura Organizacional de la Empresa' => 7,
                    'Auditoria Administrativa' => 7,
                ],
                'Noveno Cuatrimestre' => [
                    'Vida Profesional' => 4,
                    'Planeación Estratégica' => 7,
                    'Mercadotecnia de Servicios' => 6,
                    'TÓPICO I' => 7,
                    'TÓPICO II' => 7,
                ],
                'Décimo Cuatrimestre' => [
                    'Seminario de Dirección Estratégica' => 6,
                    'Consultoría Administrativa' => 6,
                    'Panorama Internacional de la Empresa' => 8,
                    'Tópico III' => 7,
                    'Tópico IV' => 7,
                ],
            ],
            'MERCADOTECNIA' => [
                'Primer Cuatrimestre' => [
                    'Habilidades de comunicación y expresión creativa' => 4,
                    'Tecnologías de la Información y de la Comunicación' => 7,
                    'Administración' => 7,
                    'Derecho I' => 7,
                    'Metodología de la Investigación' => 7,
                    'Introducción Financiera' => 7,
                    'Inglés I' => 4,
                    'Cultura y Deportes I' => 4,
                ],
                'Segundo Cuatrimestre' => [
                    'Habilidades de comunicación en el ámbito profesional' => 4,
                    'Mercadotecnia' => 7,
                    'Administración, Innovación y Modelos de Negocios' => 7,
                    'Derecho II' => 7,
                    'Matemáticas Aplicadas a los Negocios' => 7,
                    'Contabilidad I' => 7,
                    'Inglés II' => 4,
                    'Cultura y Deportes II' => 4,
                ],
                'Tercer Cuatrimestre' => [
                    'Ética En Los Negocios' => 4,
                    'Comportamiento del Consumidor' => 7,
                    'Responsabilidad Social Empresarial y Desarrollo Sostenible' => 7,
                    'Microeconomía' => 7,
                    'Probabilidad y Estadística aplicada a los negocios' => 7,
                    'Contabilidad II' => 7,
                    'Inglés III' => 4,
                    'Cultura y Deportes III' => 4,
                ],
                'Cuarto Cuatrimestre' => [
                    'Desarrollo Humano e inteligencia emocional' => 4,
                    'Investigación de Mercados Cualitativa' => 7,
                    'Macroeconomía' => 7,
                    'Diseño Gráfico' => 7,
                    'Finanzas Aplicadas a la toma de Decisiones' => 7,
                    'Inglés IV' => 4,
                    'Comunicación Organizacional (Interna y Externa)' => 7,
                ],
                'Quinto Cuatrimestre' => [
                    'Habilidades de Liderazgo' => 4,
                    'Administración y Procesos de Ventas' => 7,
                    'Negociación y Comercio Internacional' => 7,
                    'Inglés V' => 4,
                    'Competitividad Global' => 7,
                    'Investigación de Mercados Cuantitativa' => 7,
                ],
                'Sexto Cuatrimestre' => [
                    'Emprendimiento I' => 4,
                    'Inteligencia de Mercados' => 7,
                    'Sistemas de Información Gerencial' => 7,
                    'Mercadotecnia Digital I' => 7,
                    'Evaluación de Proyectos y Fuentes de Financiamiento' => 7,
                    'Inglés VI' => 4,
                    'Residencia Profesional' => 17,
                ],
                'Séptimo Cuatrimestre' => [
                    'Emprendimiento II' => 4,
                    'Mercadotecnia Digital II' => 7,
                    'Mercadotecnia Estratégica entre Negocios' => 7,
                    'Estrategias de Distribución y Comercialización' => 7,
                    'Publicidad y Promoción' => 7,
                ],
                'Octavo Cuatrimestre' => [
                    'Finanzas Personales y Empresariales' => 4,
                    'Relaciones Públicas' => 7,
                    'Producción de Medios interactivos' => 7,
                    'Mercadotecnia Internacional' => 7,
                    'Control de Presupuestos' => 7,
                ],
                'Noveno Cuatrimestre' => [
                    'Vida Profesional' => 4,
                    'Mercadotecnia de Servicios' => 7,
                    'Seminario de Comunicación Integral' => 7,
                    'Tópico I' => 7,
                    'Tópico II' => 7,
                ],
                'Décimo Cuatrimestre' => [
                    'Seminario Integrador de Mercadotecnia Estratégica' => 7,
                    'Desarrollo de Marcas y Nuevos Productos' => 7,
                    'Estrategia de Precios' => 7,
                    'Tópico III' => 7,
                    'Tópico IV' => 7,
                ],
            ],
            'DERECHO ESCOLARIZADA' => [
                'Cuarto Cuatrimestre' => [
                    'Desarrollo Humano e inteligencia emocional' => 4,
                    'Inglés IV' => 4,
                    'Teoría de los Derechos Humanos' => 7,
                    'Derecho Mercantil III Sociedades Mercantiles' => 7,
                    'Derecho Civil III Obligaciones' => 7,
                    'Derecho Penal Acusatorio y Oral' => 7,
                    'Derecho Administrativo II Administración Pública y el Municipio' => 7,
                ],
                'Quinto Cuatrimestre' => [
                    'Habilidades de Liderazgo' => 4,
                    'Inglés V' => 4,
                    'Derechos Fundamentales y Garantías Constitucionales' => 7,
                    'Derecho Procesal Mercantil' => 7,
                    'Derecho Civil IV Contratos Civiles' => 7,
                    'Prácticas de Juicios Orales' => 7,
                    'Derecho Procesal Administrativo' => 7,
                ],
                'Sexto Cuatrimestre' => [
                    'Emprendimiento I' => 4,
                    'Inglés VI' => 4,
                    'Juicio de Amparo' => 7,
                    'Derecho Laboral I Derecho Social y Relaciones Interiores de Trabajo' => 7,
                    'Derecho Procesal Civil' => 7,
                    'Ejecución de penas y medidas de seguridad' => 7,
                    'Derecho Financiero' => 7,
                    'Residencia Profesional' => 17,
                ],
                'Séptimo Cuatrimestre' => [
                    'Emprendimiento II' => 4,
                    'Prácticas de Amparo' => 7,
                    'Derecho Laboral II Derecho Colectivo del Trabajo' => 7,
                    'Derecho Procesal Familiar' => 7,
                    'Tópicos de Actualización I' => 7,
                    'Derecho Corporativo' => 7,
                ],
                'Octavo Cuatrimestre' => [
                    'Finanzas Personales y Empresariales' => 4,
                    'Derecho Notarial' => 7,
                    'Seguridad Social' => 7,
                    'Tópicos de Actualización II' => 7,
                    'Derecho Fiscal' => 7,
                    'Derecho Bancario y Bursátil' => 7,
                ],
                'Noveno Cuatrimestre' => [
                    'Vida Profesional' => 4,
                    'Derecho Registral' => 7,
                    'Derecho Procesal Laboral' => 7,
                    'Derecho Internacional Público y Privado' => 7,
                    'Derecho Procesal Fiscal' => 7,
                    'Tópicos de Actualización III' => 7,
                ],
                'Décimo Cuatrimestre' => [
                    'Seminario de Investigación' => 7,
                    'Interpretación Jurídica' => 7,
                    'Argumentación Jurídica' => 7,
                    'Justicia Alternativa' => 7,
                    'Tópicos de Actualización IV' => 7,
                    'Derecho Agrario' => 7,
                ],
            ],
            'INGENIERO ARQUITECTO' => [
                'Primer Cuatrimestre' => [
                    'Inglés I' => 4,
                    'Teoría de la Arquitectura I' => 6,
                    'Psicología del Espacio y del Color' => 4,
                    'Fundamentos para el Diseño I' => 4,
                    'Geometría Descriptiva I' => 8,
                    'Taller de Lenguaje Arquitectónico I' => 4,
                    'Habilidades de comunicación y expresión creativa' => 4,
                    'Cultura y Deportes I' => 4,
                ],
                'Segundo Cuatrimestre' => [
                    'Teoría de la Arquitectura II' => 6,
                    'Fundamentos para el Diseño II' => 4,
                    'Geometría Descriptiva II' => 8,
                    'Matemáticas para Ingeniería' => 10,
                    'Topografía' => 4,
                    'Habilidades de comunicación en el ámbito profesional' => 4,
                    'Inglés II' => 4,
                    'Cultura y Deportes II' => 4,
                ],
                'Tercer Cuatrimestre' => [
                    'Ética En Los Negocios' => 4,
                    'Inglés III' => 4,
                    'Cultura y Deportes III' => 4,
                    'Teoría de la Arquitectura III' => 6,
                    'Taller de Maquetas' => 3,
                    'Taller de Diseño I' => 10,
                    'Estructuras I' => 6,
                    'Instalaciones Hidrosanitarias y de Gas' => 6,
                ],
                'Cuarto Cuatrimestre' => [
                    'Desarrollo Humano e inteligencia emocional' => 4,
                    'Inglés IV' => 4,
                    'Teoría de la Arquitectura IV' => 6,
                    'Taller de Lenguaje Arquitectónico II' => 4,
                    'Taller de Diseño II' => 10,
                    'Estructuras II' => 6,
                    'Instalaciones Eléctricas, de Voz y Datos' => 6,
                ],
                'Quinto Cuatrimestre' => [
                    'Habilidades de Liderazgo' => 4,
                    'Inglés V' => 4,
                    'Teoría de la Sustentabilidad' => 4,
                    'Taller de Lenguaje Arquitectónico III' => 4,
                    'Taller de Diseño III' => 10,
                    'Estructuras III' => 6,
                    'Instalaciones Especiales' => 6,
                ],
                'Sexto Cuatrimestre' => [
                    'Emprendimiento I' => 4,
                    'Inglés VI' => 4,
                    'Domótica Aplicada a la Construcción' => 4,
                    'Taller de Lenguaje Arquitectónico IV' => 4,
                    'Taller de Diseño IV' => 10,
                    'Estructuras IV' => 6,
                    'Materiales y Sistemas Constructivos I' => 4,
                ],
                'Séptimo Cuatrimestre' => [
                    'Emprendimiento II' => 4,
                    'Historia de la Arquitectura y el Arte I' => 6,
                    'Taller de Lenguaje Arquitectónico V' => 4,
                    'Taller de Diseño V' => 10,
                    'Estructuras V' => 6,
                    'Materiales y Sistemas Constructivos II' => 4,
                ],
                'Octavo Cuatrimestre' => [
                    'Finanzas Personales y Empresariales' => 4,
                    'Historia de la Arquitectura y el Arte II' => 6,
                    'Urbanismo I' => 6,
                    'Presentación de Proyectos' => 3,
                    'Taller de Diseño VI' => 10,
                ],
                'Noveno Cuatrimestre' => [
                    'Vida Profesional' => 4,
                    'Historia de la Arquitectura y el Arte III' => 6,
                    'Urbanismo II' => 6,
                    'Taller de Diseño VII' => 10,
                    'Análisis de Precio Unitarios y Costos I' => 4,
                ],
                'Décimo Cuatrimestre' => [
                    'Historia de la Arquitectura y el Arte IV' => 6,
                    'Urbanismo III' => 6,
                    'Taller de Diseño VIII' => 10,
                    'Administración de Obra I' => 6,
                    'Análisis de Precio Unitarios y Costos II' => 4,
                    'Tópicos de Actualización I' => 4,
                ],
                'Undécimo Cuatrimestre' => [
                    'Historia de la Arquitectura y el Arte V' => 6,
                    'Urbanismo IV' => 6,
                    'Taller de Diseño IX' => 10,
                    'Administración de Obra II' => 6,
                    'Ingeniería de Costos' => 4,
                    'Tópicos de Actualización II' => 4,
                ],
                'Duodécimo Cuatrimestre' => [
                    'Historia de la Arquitectura y el Arte VI' => 6,
                    'Taller de Diseño X' => 10,
                    'Empresas Constructoras I' => 6,
                    'Tópicos de Actualización III' => 4,
                ],
                'Decimotercer Cuatrimestre' => [
                    'Taller de Diseño Urbano' => 10,
                    'Empresas Constructoras II' => 6,
                    'Tópicos de Actualización IV' => 4,
                ],
                'Decimocuarto Cuatrimestre' => [
                    'RESIDENCIAS EMPRESARIALES' => 22,
                ],
            ],

            'ADMON. NEG. INT.' => [
                'Primer Semestre' => [
                    'Materia Sello' => 8,
                    'Introducción al Estudio del Derecho' => 6,
                    'Metodología de la Investigación' => 6,
                    'Administración' => 6,
                    'Innovación y Emprendimiento I' => 6,
                    'Contabilidad para los Negocios' => 6,
                    'Introducción a la Mercadotecnia' => 6,
                    'Microeconomía' => 6,
                    'Idioma I' => 8,
                ],
                'Segundo Semestre' => [
                    'Materia Sello' => 8,
                    'Administración y Proceso de Venta' => 4,
                    'Marco Legal Empresarial' => 7,
                    'Matemáticas e Inteligencia Aplicadas a los Negocios' => 6,
                    'Geografía, Historia de México y Mundial' => 6,
                    'Introducción a las Finanzas' => 6,
                    'Management 4.0' => 5,
                    'Macroeconomía y Macrotendencias en las Industrias' => 6,
                    'Idioma II' => 8,
                ],
                'Tercer Semestre' => [
                    'Materia Sello' => 8,
                    'Responsabilidad Social, Empresarial y Sustentabilidad' => 5,
                    'Optativa' => 8,
                    'Finanzas Aplicadas a la Toma de Decisiones' => 6,
                    'Ética en los Negocios' => 4,
                    'Administración de la Calidad' => 6,
                    'Comportamiento del Consumidor y Ciencia de Datos' => 6,
                    'Probabilidad y Estadística Aplicadas a los Negocios' => 6,
                    'Idioma III' => 8,
                ],
                'Cuarto Semestre' => [
                    'Materia Sello' => 8,
                    'Contabilidad de Costos' => 6,
                    'La Era de la Hiper-personalización y el Marketing Digital' => 4,
                    'Administración de Operaciones' => 6,
                    'Neuromarketing y Experiencia del Cliente' => 4,
                    'Empresa y Competitividad Global' => 4,
                    'Comportamiento y Cultura Organizacional' => 6,
                    'Derecho Laboral' => 4,
                    'Idioma IV' => 8,
                ],
                'Quinto Semestre' => [
                    'Materia Sello' => 8,
                    'Inclusión y Diversidad en los Negocios' => 6,
                    'Compras y Entornos Internacionales' => 4,
                    'Logística y Cadena de Abastecimiento' => 6,
                    'Comunicación Organizacional y Reputación de la Marca' => 6,
                    'Economía Internacional' => 6,
                    'Finanzas Corporativas' => 6,
                    'Innovación y Emprendimiento II' => 6,
                    'Segundo Idioma I' => 8,
                ],
                'Sexto Semestre' => [
                    'Materia Sello' => 8,
                    'Gestión del Talento Humano' => 6,
                    'Habilidades Directivas' => 4,
                    'Modelo de Negocios' => 6,
                    'Estrategias de Comercialización y Posicionamiento' => 4,
                    'Evaluación de Proyectos y Fuentes de Financiamiento' => 6,
                    'Dirección Estratégica Internacional' => 4,
                    'Tratados Internacionales' => 4,
                    'Segundo Idioma II' => 8,
                ],
                'Séptimo Semestre' => [
                    'Materia Sello' => 8,
                    'Técnicas de Negociación' => 6,
                    'Planeación Estratégica' => 6,
                    'Análisis de Mercados Emergentes' => 6,
                    'Comercialización y Distribución' => 6,
                    'Aeropuertos y Puertos' => 6,
                    'Segundo Idioma III' => 8,
                ],
                'Octavo Semestre' => [
                    'Materia Sello' => 8,
                    'Auditoría Administrativa' => 4,
                    'Análisis de Casos' => 4,
                    'Negocios Digitales (E-Business)' => 4,
                    'Seminario Integrador de Negocios Internacionales' => 4,
                    'Comercio Internacional' => 6,
                    'Segundo Idioma IV' => 8,
                ],
                'Noveno Semestre' => [
                    'Prácticas Profesionales' => 22,
                    'Programas de Fomento a la Exportación' => 6,
                    'Mercadotecnia Internacional' => 6,
                    'Taller de Simulación de Negocios' => 4,
                    'Tributación para los Negocios Internacionales' => 6,
                    'Taller de Diseño de Experiencia de Cliente' => 7,
                    'Proyecto Integrador' => 6,
                ],
            ],
            'DERECHO' => [
                'Primer Semestre' => [
                    'Materia Sello' => 8,
                    'Sociología' => 6,
                    'Derecho Romano' => 6,
                    'Introducción al Derecho' => 8,
                    'Derecho y Justicia' => 6,
                    'Teoría del Estado' => 6,
                    'Metodología de la Investigación' => 6,
                    'Oralidad' => 6,
                    'Idioma I' => 8,
                ],
                'Segundo Semestre' => [
                    'Materia Sello' => 8,
                    'Teoría General del Proceso' => 6,
                    'De las Personas y Familia' => 6,
                    'Derecho Penal I Teoría del Delito' => 6,
                    'Derecho Constitucional I' => 8,
                    'Derecho Laboral I' => 6,
                    'Investigación Jurídica' => 6,
                    'Idioma II' => 8,
                ],
                'Tercer Semestre' => [
                    'Materia Sello' => 8,
                    'Derecho Administrativo I' => 6,
                    'De los Bienes y Derechos Reales' => 6,
                    'Derecho Penal II Delitos en Especial' => 6,
                    'Derecho Constitucional II' => 8,
                    'Derecho Laboral II' => 6,
                    'Derecho Internacional Público' => 6,
                    'Idioma III' => 8,
                ],
                'Cuarto Semestre' => [
                    'Materia Sello' => 8,
                    'Derecho Procesal Civil' => 6,
                    'De las Obligaciones I' => 6,
                    'Derecho Internacional Privado' => 6,
                    'Derecho Administrativo II' => 6,
                    'Derecho Procesal Penal' => 6,
                    'Derechos Humanos' => 8,
                    'Idioma IV' => 8,
                ],
                'Quinto Semestre' => [
                    'Materia Sello' => 8,
                    'De la Obligaciones II' => 6,
                    'Contratos Civiles' => 6,
                    'Derecho Fiscal' => 8,
                    'Contratos Mercantiles' => 7,
                    'Juicio Oral' => 8,
                    'Derecho Ambiental' => 6,
                ],
                'Sexto Semestre' => [
                    'Materia Sello' => 8,
                    'Amparo I' => 6,
                    'Títulos y Operaciones de Crédito' => 7,
                    'Casos de Práctica Forense de Derecho Laboral' => 6,
                    'De las Sucesiones' => 6,
                    'Patentes y Marcas' => 7,
                ],
                'Séptimo Semestre' => [
                    'Materia Sello' => 8,
                    'Amparo II' => 6,
                    'Derecho Procesal Fiscal y Administrativo' => 6,
                    'Casos de Práctica Forense del Sistema Penal Acusatorio' => 6,
                    'Derecho Procesal Mercantil' => 7,
                ],
                'Octavo Semestre' => [
                    'Materia Sello' => 8,
                    'Casos de Práctica Forense de Amparo' => 6,
                    'Casos de Práctica Forense de Juicios Orales' => 6,
                    'Iniciativa Legal I' => 6,
                    'Tratados Internacionales de Derechos Humanos' => 8,
                ],
                'Noveno Semestre' => [
                    'Prácticas Profesionales' => 22,
                    'Solución de Controversias' => 6,
                    'Ética Jurídica' => 6,
                    'Iniciativa Legal II' => 6,
                    'Argumentación Jurídica' => 6,
                ],
            ],
            'DISEÑO IND.DES.PROD.' => [
                'Primer Semestre' => [
                    'Materia Sello' => 8,
                    'Exploración de la Forma y El Espacio' => 6,
                    'Fundamentos de Dibujo' => 4,
                    'Geometría I' => 6,
                    'Historia del Arte' => 3,
                    'Fundamentos del Diseño I' => 5,
                    'Matemáticas para el Diseño' => 3,
                    'Idioma I' => 8,
                ],
                'Segundo Semestre' => [
                    'Materia Sello' => 8,
                    'Exploración de Materiales' => 5,
                    'Bocetado Como Comunicación Gráfica' => 4,
                    'Especificación Técnica Análoga De La Forma' => 6,
                    'Teorías e Historia del Diseño' => 3,
                    'Fundamentos del Diseño II' => 5,
                    'Física para el Diseño' => 3,
                    'Idioma II' => 8,
                ],
                'Tercer Semestre' => [
                    'Materia Sello' => 8,
                    'Tecnología de Materiales (Maderas)' => 6,
                    'Comunicación Gráfica Digital' => 6,
                    'Modelado y Especificación Técnica Computarizados' => 6,
                    'Antropometría' => 3,
                    'Taller de Diseño de Producto' => 6,
                    'Diseño como Propuesta de Valor' => 3,
                    'Idioma III' => 8,
                ],
                'Cuarto Semestre' => [
                    'Materia Sello' => 8,
                    'Tecnología de Materiales (Metales)' => 6,
                    'Fotografía' => 4,
                    'Modelado Tridimensional Parametrizada' => 6,
                    'Métodos de Diseño y Creatividad' => 4,
                    'Taller de Diseño de Servicios' => 6,
                    'Seminario de Técnicas de Investigación' => 3,
                    'Idioma IV' => 8,
                ],
                'Quinto Semestre' => [
                    'Materia Sello' => 8,
                    'Tecnología de Materiales (Plásticos)' => 6,
                    'Animación Tridimensional Enfocada A Mecanismos' => 6,
                    'Modelado Tridimensional Por Superficies' => 6,
                    'Antropología Y Etnografía En El Diseño' => 3,
                    'Taller De Diseño Social' => 6,
                    'Modelos De Negocio Entorno Al Diseño' => 4,
                ],
                'Sexto Semestre' => [
                    'Materia Sello' => 8,
                    'Tecnología de Materiales (Cerámicos)' => 6,
                    'Manufactura Rápida Y Prototipos Tridimensional' => 4,
                    'Diseño Emocional' => 4,
                    'Biomímesis en el Diseño' => 4,
                    'Taller de Diseño Sistémico' => 6,
                    'Casos de Emprendimiento en el Diseño I' => 4,
                ],
                'Séptimo Semestre' => [
                    'Materia Sello' => 8,
                    'Optativa I' => 6,
                    'Optativa II' => 6,
                    'Tendencias y Prospectiva' => 3,
                    'Ergoecología' => 5,
                    'Taller De Diseño Para La Sostenibilidad' => 6,
                    'Validación: Producto Mínimo Viable (MVP)' => 4,
                ],
                'Octavo Semestre' => [
                    'Materia Sello' => 8,
                    'Optativa III' => 6,
                    'Optativa IV' => 6,
                    'Mercadotecnia y Venta Del Diseño' => 4,
                    'Administración del Diseño' => 4,
                    'Taller de Diseño Estratégico' => 10,
                    'Casos de Emprendimiento en el Diseño II' => 4,
                ],
                'Noveno Semestre' => [
                    'Prácticas Profesionales' => 22,
                    'Optativa V' => 6,
                    'Optativa VI' => 6,
                    'Legislación, Normas y Propiedad Intelectual' => 3,
                    'Gestión de Diseño' => 3,
                    'Taller de Diseño Integral' => 10,
                    'Economía del Diseño y Políticas Públicas' => 3,
                ],
            ],
            'PROD. ANIM. ESCOLARI' => [
                'Primer Semestre' => [
                    'Introducción al 3D' => 8,
                    'Dibujo I' => 6,
                    'Actuación' => 6,
                    'Fundamentos de Animación' => 8,
                    'Fotografía' => 6,
                    'Programación I' => 6,
                    'Materia sello I' => 8,
                    'Idioma I' => 8,
                    'Metodología de la Investigación' => 4,
                ],
                'Segundo Semestre' => [
                    'Modelado Orgánico' => 8,
                    'Esqueletos I' => 8,
                    'Animación Vectorial' => 8,
                    'Edición de Audio y Video' => 6,
                    'Programación II' => 6,
                    'Historia del Arte y Animación' => 4,
                    'Materia sello II' => 10,
                    'Idioma II' => 8,
                ],
                'Tercer Semestre' => [
                    'Modelado Inorgánico' => 8,
                    'Dibujo II' => 6,
                    'Esqueletos II' => 8,
                    'Postproducción' => 6,
                    'Diseño Interactivo' => 6,
                    'Análisis Discurso Audiovisual' => 6,
                    'Materia sello III' => 10,
                    'Idioma III' => 8,
                ],
                'Cuarto Semestre' => [
                    'Escultura Digital I' => 8,
                    'Gráficos en Movimiento' => 6,
                    'Composición Digital' => 6,
                    'Arte conceptual' => 6,
                    'Lenguaje Cinematográfico' => 6,
                    'Producción' => 6,
                    'Materia sello IV' => 10,
                    'Idioma IV' => 8,
                ],
                'Quinto Semestre' => [
                    'Escultura Digital II' => 8,
                    'Animación 3D I' => 8,
                    'Doblaje' => 6,
                    'Taller de video juegos I' => 6,
                    'Guionismo' => 6,
                    'Negocios de la Animación I' => 6,
                    'Materia sello V' => 10,
                ],
                'Sexto Semestre' => [
                    'Texturas y Materiales 3D I' => 8,
                    'Realidad Aumentada y Virtual I' => 6,
                    'Animación de Dinámicos I' => 8,
                    'Musicalización y Diseño Sonoro I' => 6,
                    'Guión visual' => 6,
                    'Negocios de la Animación II' => 6,
                    'Materia sello VI' => 10,
                ],
                'Séptimo Semestre' => [
                    'Animación 3D II' => 8,
                    'Corrección de Color' => 6,
                    'Texturas y Materiales 3D II' => 8,
                    'Modelado de Escenarios' => 6,
                    'Laboratorio de Proyectos I Animación' => 4,
                    'Materia sello VII' => 10,
                ],
                'Octavo Semestre' => [
                    'Animación 3D III' => 8,
                    'Procesamiento de imágenes e Iluminación 3D I' => 8,
                    'Pintura Digital' => 6,
                    'Musicalización y Diseño Sonoro II' => 6,
                    'Laboratorio de Proyectos II Animación' => 4,
                    'Materia sello VIII' => 8,
                ],
                'Noveno Semestre' => [
                    'Proyecto Integrador Animación' => 10,
                    'Animación por captura de Movimiento' => 8,
                    'Post producción Avanzada' => 8,
                    'Procesamiento de imágenes e Iluminación 3D II' => 8,
                    'Laboratorio de Proyectos III Animación' => 4,
                ],
            ],
        ];

        // Verifica si la carrera existe
        if (!array_key_exists($carrera, $carreras)) {
            abort(404, 'Carrera no encontrada.');
        }

        $cuatrimestres = $carreras[$carrera];

        $query = DatosCalificaciones::where('descripcion_breve', $carrera);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('apellidos_nombre', 'like', '%' . $search . '%')
                    ->orWhere('matricula', 'like', '%' . $search . '%');
            });
        }

        $datos = $query->get();
        $cicloMasReciente = $this->obtenerCicloMasReciente($datos);
        $calificaciones = $this->procesarCalificaciones($datos, $cuatrimestres);
        $nombresAlumnos = $datos->pluck('apellidos_nombre', 'matricula');
        $promediosCuatrimestresPorAlumno = $this->calcularPromediosPorCuatrimestre($calificaciones, $cuatrimestres);
        $promedioGeneralPorAlumno = $this->calcularPromedioGeneral($promediosCuatrimestresPorAlumno);
        $materiasReprobadasPorAlumno = $this->calcularMateriasReprobadas($calificaciones, $cuatrimestres);

        $creditosTotales = collect($cuatrimestres)->flatten()->sum();

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

        $ciclosExistentes = $datos->pluck('ciclo')->unique()->sort()->values()->all();

        return view('reportes/calificaciones/carreras/calificacionesCarrera', compact(
            'carrera',
            'cuatrimestres',
            'calificaciones',
            'nombresAlumnos',
            'search',
            'selectedCiclo',
            'promediosCuatrimestresPorAlumno',
            'promedioGeneralPorAlumno',
            'materiasReprobadasPorAlumno',
            'cicloMasReciente',
            'creditosTotales',
            'creditosCursadosPorAlumno',
            'creditosFaltantesPorAlumno',
            'ciclosExistentes'
        ));
    }

}
