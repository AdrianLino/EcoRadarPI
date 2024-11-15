<div class="container">
    <h1>Calificaciones - Administración de Negocios Mixta</h1>

    <!-- Formulario de búsqueda y selección de ciclo -->
    <form method="GET" action="{{ route('calificaciones.admon_neg_mixta') }}" class="mb-4" id="cicloForm">
        <div class="form-row">
            <div class="col">
                <label for="search">Buscar Alumno (por nombre o matrícula):</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Escribe el nombre o matrícula" value="{{ $search }}">
            </div>
            <div class="col">
                <label for="selected_ciclo">Seleccionar Ciclo:</label>
                <select name="selected_ciclo" id="selected_ciclo" class="form-control">
                    @foreach($ciclosExistentes as $ciclo)
                        <option value="{{ $ciclo }}" {{ $ciclo === $selectedCiclo ? 'selected' : '' }}>{{ $ciclo }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Aplicar Filtros</button>
        <a href="{{ route('calificaciones.admon_neg_mixta') }}" class="btn btn-secondary mt-3">Restablecer</a>
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
                @foreach($materias as $materia => $creditos)
                    <tr>
                        <td>{{ $materia }}</td>
                        @foreach($calificaciones as $matricula => $materiasAlumno)
                            @php
                                $calificacion = $materiasAlumno[$materia] ?? null;
                                $promedioP = $calificacion['promedio_p'] ?? null;
                                $cicloP = $calificacion['ciclo_p'] ?? null;
                                $cicloOtro = $calificacion['ciclo_otro'] ?? null;
                                $isHighlighted = $selectedCiclo && ($cicloP === $selectedCiclo || $cicloOtro === $selectedCiclo);
                                $isRecentCiclo = ($cicloP === $cicloMasReciente || $cicloOtro === $cicloMasReciente);
                                $evaluationType = $calificacion['evaluation_type'] ?? null;
                            @endphp
                            <td class="calificacion" style="background-color: {{ $isHighlighted ? 'blue' : ($promedioP !== null && $promedioP < 6 ? 'red' : ($isRecentCiclo ? 'lightblue' : 'transparent')) }};">
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

            <!-- Fila de créditos totales, cursados y faltantes para cada alumno -->
            <tr class="font-weight-bold">
                <td>Créditos Totales</td>
                @foreach($calificaciones as $matricula => $materiasAlumno)
                    <td class="text-center">{{ $creditosTotales }}</td>
                @endforeach
                <td></td>
            </tr>
            <tr class="font-weight-bold">
                <td>Créditos Cursados</td>
                @foreach($calificaciones as $matricula => $materiasAlumno)
                    <td class="text-center">{{ $creditosCursadosPorAlumno[$matricula] ?? 0 }}</td>
                @endforeach
                <td></td>
            </tr>
            <tr class="font-weight-bold">
                <td>Créditos Faltantes</td>
                @foreach($calificaciones as $matricula => $materiasAlumno)
                    <td class="text-center">{{ $creditosFaltantesPorAlumno[$matricula] ?? 0 }}</td>
                @endforeach
                <td></td>
            </tr>
        </tbody>
    </table>
</div>



<script>
    document.getElementById('selected_ciclo').addEventListener('change', function () {
        document.getElementById('cicloForm').submit();
    });
</script>