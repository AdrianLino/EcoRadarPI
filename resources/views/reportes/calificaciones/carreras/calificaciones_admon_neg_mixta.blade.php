<div class="container">
    <h1>Calificaciones - Administración de Negocios Mixta</h1>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="{{ route('calificaciones.admon_neg_mixta') }}" class="mb-4">
        <div class="form-group">
            <label for="search">Buscar Alumno (por nombre o matrícula):</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Escribe el nombre o matrícula" value="{{ $search }}">
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
        <a href="{{ route('calificaciones.admon_neg_mixta') }}" class="btn btn-secondary">Restablecer</a>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th rowspan="2">Materia</th>
                @foreach($calificaciones as $matricula => $materias)
                    <th>{{ $nombresAlumnos[$matricula] ?? 'N/A' }}</th>
                @endforeach
                <th rowspan="2">Promedio Cuatrimestre</th>
            </tr>
            <tr>
                @foreach($calificaciones as $matricula => $materias)
                    <th>{{ $matricula }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($cuatrimestres as $cuatrimestre => $materias)
                <tr>
                    <td colspan="{{ $calificaciones->count() + 2 }}" class="text-center font-weight-bold">{{ $cuatrimestre }}</td>
                </tr>
                @foreach($materias as $materia)
                    <tr>
                        <td>{{ $materia }}</td>
                        @foreach($calificaciones as $matricula => $materiasAlumno)
                            @php
                                $calificacion = $materiasAlumno[$materia] ?? null;
                                $promedioP = $calificacion['promedio_p'] ?? null;
                                $cicloP = $calificacion['ciclo_p'] ?? null;
                                $cicloOtro = $calificacion['ciclo_otro'] ?? null;
                                $isRecentCiclo = ($cicloP === $cicloMasReciente || $cicloOtro === $cicloMasReciente);
                                $evaluationType = $calificacion['evaluation_type'] ?? null;

                            @endphp
                            <td class="calificacion" style="background-color: {{ $promedioP !== null && $promedioP < 6 ? 'red' : ($isRecentCiclo ? 'lightblue' : 'transparent') }};">
                                @if($promedioP !== null)
                                    <span class="promedio_p">{{ $promedioP }}</span>
                                    <small>({{ $evaluationType }})</small>
                                    @if($cicloP)
                                        <small>({{ $cicloP }})</small>
                                    @elseif($cicloOtro)
                                        <small>({{ $cicloOtro }})</small>
                                    @endif
                                @else
                                    
                                @endif
                            </td>
                        @endforeach
                        <td></td> <!-- Espacio vacío para mantener alineación con promedio cuatrimestral -->
                    </tr>
                @endforeach

                <!-- Fila del promedio del cuatrimestre para cada alumno -->
                <tr class="font-weight-bold">
                    <td>Promedio {{ $cuatrimestre }}</td>
                    @foreach($calificaciones as $matricula => $materiasAlumno)
                        <td class="text-center">{{ $promediosCuatrimestresPorAlumno[$matricula][$cuatrimestre] ?? 'N/A' }}</td>
                    @endforeach
                    <td></td>
                </tr>
            @endforeach

            <!-- Fila del promedio general y materias reprobadas para cada alumno -->
            <tr class="font-weight-bold">
                <td>Promedio General</td>
                @foreach($calificaciones as $matricula => $materiasAlumno)
                    <td class="text-center">{{ $promedioGeneralPorAlumno[$matricula] ?? 'N/A' }}</td>
                @endforeach
                <td></td>
            </tr>
            <tr class="font-weight-bold">
                <td>Materias Reprobadas</td>
                @foreach($calificaciones as $matricula => $materiasAlumno)
                    <td class="text-center">{{ $materiasReprobadasPorAlumno[$matricula] ?? 0 }}</td>
                @endforeach
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
