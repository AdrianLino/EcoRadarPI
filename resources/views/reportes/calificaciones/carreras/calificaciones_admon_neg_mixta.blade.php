<div class="container">
    <h1>Calificaciones - {{ $carrera }}</h1>
    <p>Ciclo más reciente: {{ $cicloMasReciente }}</p>

    <!-- Tabla de calificaciones -->
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
        @foreach ($cuatrimestres as $cuatrimestre => $materias)
    <tr>
        <td>{{ $cuatrimestre }}</td>
        @foreach ($calificaciones as $matricula => $materiasAlumno)
            @php
                $calificacion = $materiasAlumno[$materia] ?? null;
            @endphp
            <td>
                @if($calificacion && isset($calificacion['promedio_p']))
                    {{ $calificacion['promedio_p'] }}
                @else
                    N/A
                @endif
            </td>
        @endforeach
    </tr>
@endforeach


                <!-- Promedio del cuatrimestre -->
                <tr class="font-weight-bold">
                    <td>Promedio {{ $cuatrimestre }}</td>
                    @foreach($calificaciones as $matricula => $materiasAlumno)
                        <td class="text-center">{{ $promediosCuatrimestresPorAlumno[$matricula][$cuatrimestre] ?? 'N/A' }}</td>
                    @endforeach
                    <td></td>
                </tr>
            @endforeach

            <!-- Promedio general y materias reprobadas -->
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
