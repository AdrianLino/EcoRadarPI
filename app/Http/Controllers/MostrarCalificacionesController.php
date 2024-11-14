<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatosCalificaciones;

class MostrarCalificacionesController extends Controller
{
    public function seleccionarCarrera()
    {
        // Lista de carreras disponibles y sus nombres descriptivos
        $carreras = [
            'MERCADOTECNIA MIXTA' => 'LICENCIATURA EN MERCADOTECNIA MIXTA',
            'ADMON.NEG.MIXTA' => 'Administración de Negocios Mixta',
            'DERECHO MIXTA' => 'LICENCIATURA EN DERECHO MIXTA',
            'BIENES RAÍCES' => 'ESPECIALIDAD EN BIENES RAÍCES',
            'MERCADOTECNIA' => 'LICENCIATURA EN MERCADOTECNIA',
            'ADMON.NEG.ESCOLARI' => 'LICENCIATURA EN ADMINISTRACIÓN DE NEGOCIOS',
            'DERECHO ESCOLARIZADA' => 'LICENCIATURA EN DERECHO',
            'INGENIERO ARQUITECTO' => 'LICENCIATURA INGENIERO ARQUITECTO',
            'PROD. ANIM. ESCOLARI' => 'LICENCIATURA EN PRODUCCION DE ANIMACIÓN Y VFX',
            'ADMON.NEG.INT' => 'LICENCIATURA EN ADMINISTRACIÓN Y NEGOCIOS INT',
            'DERECHO' => 'LICENCIATURA EN DERECHO',
            'DISEÑO IND.DES.PROD' => 'LICENCIATURA EN DISEÑO INDUSTRIAL Y DES. DE PROD.',
            'ING ARQ SEM' => 'LICENCIATURA EN INGENIERO ARQUITECTO',
            'MERCA. ESTRAT. DIG' => 'LICENCIATURA EN MERCADOTECNIA Y ESTRAT. DIGITALES',
        ];

        return view('reportes/calificaciones/carreras/seleccionar_carrera', compact('carreras'));
    }

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

    public function mostrarCalificaciones(Request $request, $carrera)
    {
        // Verifica que la carrera seleccionada esté en la lista del archivo de configuración
        $carrerasMaterias = config('carrerasMaterias');
    
        if (!array_key_exists($carrera, $carrerasMaterias)) {
            return redirect()->route('calificaciones.seleccionar_carrera')->withErrors('Carrera no encontrada.');
        }
    
        $cuatrimestres = $carrerasMaterias[$carrera];
        $search = $request->input('search');
    
        $query = DatosCalificaciones::where('descripcion_breve', $carrera);
    
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('apellidos_nombre', 'like', '%' . $search . '%')
                    ->orWhere('matricula', 'like', '%' . $search . '%');
            });
        }
    
        $datos = $query->get();

        
    
        // Identificar el ciclo más reciente usando el orden CB, CC, CA o S2, S1
        $ciclos = $datos->pluck('ciclo')->unique()->filter()->sort()->values()->all();
    
        usort($ciclos, function ($a, $b) {
            [$yearA, $partA] = explode('-', $a);
            [$yearB, $partB] = explode('-', $b);
    
            if ($yearA != $yearB) {
                return $yearA <=> $yearB;
            }
    
            // Define el orden según el tipo de prefijos
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
    
        $cicloMasReciente = end($ciclos);
    
        $calificaciones = $datos->groupBy(['matricula', 'descripcion'])->map(function ($materias) {
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
                    return in_array($item->evaluacion, ['EQ', 'EXT1', 'EXT2', 'PFO']);
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
                    // Aquí verificamos si el promedio_p existe
                    if (isset($materiasAlumno[$materia]['promedio_p'])) {
                        $calificacion = $materiasAlumno[$materia]['promedio_p'];
                        if ($calificacion < 6) {
                            $reprobadas++;
                        }
                        $totalPromediosCuatrimestre += $calificacion;
                        $totalPromediosAlumno += $calificacion;
                        $totalCalificacionesCuatrimestre++;
                        $totalCalificacionesAlumno++;
                    } else {
                        // Mensaje de depuración si no encuentra una calificación
                        echo "Calificación no encontrada para $materia en el cuatrimestre $cuatrimestre para la matrícula $matricula<br>";
                    }
                }
        
                // Calcular promedio para cada cuatrimestre
                $promediosCuatrimestresPorAlumno[$matricula][$cuatrimestre] = $totalCalificacionesCuatrimestre > 0
                    ? round($totalPromediosCuatrimestre / $totalCalificacionesCuatrimestre, 1)
                    : null;
            }
        
            // Calcular promedio general del alumno
            $promedioGeneralPorAlumno[$matricula] = $totalCalificacionesAlumno > 0
                ? round($totalPromediosAlumno / $totalCalificacionesAlumno, 1)
                : null;
        
            // Guardar la cantidad de materias reprobadas
            $materiasReprobadasPorAlumno[$matricula] = $reprobadas;
        }
        

    
        return view('reportes/calificaciones/carreras/calificaciones_admon_neg_mixta', compact(
            'cuatrimestres',
            'calificaciones',
            'nombresAlumnos',
            'search',
            'promediosCuatrimestresPorAlumno',
            'promedioGeneralPorAlumno',
            'materiasReprobadasPorAlumno',
            'cicloMasReciente',
            'carrera'
        ));
    }
    
    
}
