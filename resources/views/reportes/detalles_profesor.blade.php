<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Profesor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Cargar Chart.js -->
</head>

<body>

    <h1>Detalles del Profesor: {{ $profesor }}</h1>

    <!-- Formulario para seleccionar el período -->
    <form method="GET" action="{{ route('profesores.detalles', $profesor) }}">
        <label for="periodo">Selecciona un período:</label>
        <select name="periodo" id="periodo" onchange="this.form.submit()">
            <option value="todos" {{ $periodoSeleccionado === 'todos' ? 'selected' : '' }}>Todos los períodos</option>
            @foreach ($periodos as $periodo)
                <option value="{{ $periodo }}" {{ $periodoSeleccionado === $periodo ? 'selected' : '' }}>{{ $periodo }}
                </option>
            @endforeach
        </select>
    </form>

    <!-- Mostrar los registros del profesor -->
    <table border="1">
        <thead>
            <tr>
                <th>Periodo</th>
                <th>Grupo</th>
                <th>Dirección</th>
                <th>Asignatura</th>
                <th>Evaluación</th>
                <th>Encuestas</th>
                <th>Comentarios</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registros as $registro)
                        <tr>
                            <td>{{ $registro->periodo }}</td>
                            <td>{{ $registro->grupo }}</td>
                            <td>{{ $registro->direccion }}</td>
                            <td>{{ $registro->asignatura }}</td>
                            <td>{{ $registro->evaluacion }}</td>
                            <td>{{ $registro->encuestas }}</td>
                            <td>
                                @php
                                    $comentarios = [
                                        $registro->comentario_1,
                                        $registro->comentario_2,
                                        $registro->comentario_3,
                                        $registro->comentario_4,
                                        $registro->comentario_5,
                                        $registro->comentario_6,
                                        $registro->comentario_7,
                                        $registro->comentario_8,
                                        $registro->comentario_9,
                                        $registro->comentario_10,
                                        $registro->comentario_11,
                                        $registro->comentario_12,
                                        $registro->comentario_13
                                    ];
                                    $comentarios = array_filter($comentarios);
                                @endphp

                                @if (count($comentarios) > 0)
                                    <ul>
                                        @foreach ($comentarios as $comentario)
                                            <li>{{ $comentario }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    No hay comentarios
                                @endif
                            </td>
                        </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Gráfica de barras para las respuestas del 1 al 19 relacionadas con las preguntas -->
    <div style="width: 100%; height: 500px; margin-top: 50px;">
        <canvas id="respuestaChart"></canvas> <!-- Aquí se renderizará la gráfica -->
    </div>

    <!-- Gráfica de barras para el Promedio de Evaluación por Período -->
    <div style="width: 100%; height: 500px; margin-top: 50px;">
        <canvas id="evaluacionChart"></canvas> <!-- Aquí se renderizará la gráfica de períodos -->
    </div>

    <!-- Ranking de las mejores y peores respuestas -->
    <div style="width: 100%; margin-top:
<!-- Ranking de las mejores y peores respuestas -->
<div style=" width: 100%; margin-top: 50px; display: flex; justify-content: space-between;">
        <!-- Tabla de las 3 mejores respuestas -->
        <div style="width: 45%;">
            <h3>Top 5 Mejores Promedios</h3>
            <table border="1">
                <thead>
                    <tr>
                        <th>Pregunta</th>
                        <th>Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mejoresRespuestas as $key => $valor)
                        <tr>
                            <td>{{ $preguntas[(int) str_replace('respuesta_', '', $key) - 1] }}</td>
                            <td>{{ round($valor, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tabla de las 3 peores respuestas -->
        <div style="width: 45%;">
            <h3>Top 5 Peores Promedios</h3>
            <table border="1">
                <thead>
                    <tr>
                        <th>Pregunta</th>
                        <th>Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($peoresRespuestas as $key => $valor)
                        <tr>
                            <td>{{ $preguntas[(int) str_replace('respuesta_', '', $key) - 1] }}</td>
                            <td>{{ round($valor, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script para crear las gráficas -->
    <script>
        // Gráfica de promedio de respuestas por pregunta
        var ctx1 = document.getElementById('respuestaChart').getContext('2d');

        var chart1 = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: {!! json_encode($preguntas) !!},  // Las preguntas en el eje X
                datasets: [{
                    label: 'Promedio de Respuestas (Periodo: {{ $periodoSeleccionado }})',
                    data: {!! json_encode(array_values($promediosRespuestas)) !!}, // Promedios de las respuestas 1 a 19
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Gráfica de promedio de evaluación por período
        var ctx2 = document.getElementById('evaluacionChart').getContext('2d');

        var chart2 = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: {!! json_encode($promediosPorPeriodo->keys()) !!},  // Los períodos ordenados
                datasets: [{
                    label: 'Promedio de Evaluación por Período',
                    data: {!! json_encode($promediosPorPeriodo->values()) !!},  // Los promedios de evaluación por período
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>

    <a href="{{ route('profesores.listar') }}">Volver a la lista de profesores</a>

</body>

</html>