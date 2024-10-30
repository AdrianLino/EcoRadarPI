<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Profesor: ') }} {{ $profesor }}
            </h2>
    
            <a href="{{ route('profesores.listar') }}" class="ml-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                Volver a la lista de profesores
            </a>
        </div>
    </x-slot>
    

    <!-- Formulario para seleccionar el período -->

    <form method="GET" action="{{ route('profesores.detalles', $profesor) }}" class="max-w-sm mx-auto m-6">
        <label for="periodo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Selecciona un período</label>
        <select name="periodo" id="periodo" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <option value="todos" {{ $periodoSeleccionado === 'todos' ? 'selected' : '' }}>Todos los períodos</option>
            @foreach ($periodos as $periodo)
                <option value="{{ $periodo }}" {{ $periodoSeleccionado === $periodo ? 'selected' : '' }}>{{ $periodo }}</option>
            @endforeach
        </select>
    </form>
    

    <!-- Mostrar los registros del profesor -->    
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Periodo
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Grupo
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Dirección
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Asignatura
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Evaluación
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Encuestas
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Comentarios
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $registro)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4">
                            {{ $registro->periodo }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $registro->grupo }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $registro->direccion }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $registro->asignatura }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $registro->evaluacion }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $registro->encuestas }}
                        </td>
                        <td class="px-6 py-4">
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
                                <ul class="list-disc pl-5">
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
    </div>

    <!-- Gráfica de barras para el Promedio de Evaluación por Período -->
<div class="w-full max-w-4xl mx-auto mt-10">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <canvas id="evaluacionChart"></canvas> <!-- Aquí se renderizará la gráfica de períodos -->
    </div>
</div>
    



<!-- Gráfica de barras para las respuestas del 1 al 19 relacionadas con las preguntas -->
<div class="w-full h-[75vh] mx-auto mt-10 flex justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 w-full h-full">
        <canvas id="respuestaChart"></canvas> <!-- Aquí se renderizará la gráfica -->
    </div>
</div>





<!-- Ranking de las mejores y peores respuestas -->
<div class="w-full mt-10 flex justify-center">
    <!-- Tabla de los 5 mejores promedios -->
    <div class="w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4 text-center">Top 5 Mejores Promedios</h3>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Pregunta
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Promedio
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mejoresRespuestas as $key => $valor)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $preguntas[(int) str_replace('respuesta_', '', $key) - 1] }}
                            </td>
                            <td class="px-6 py-4">
                                {{ round($valor, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabla de los 5 peores promedios -->
    <div class="w-full max-w-lg ml-6">
        <h3 class="text-lg font-semibold mb-4 text-center">Top 5 Peores Promedios</h3>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Pregunta
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Promedio
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($peoresRespuestas as $key => $valor)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $preguntas[(int) str_replace('respuesta_', '', $key) - 1] }}
                            </td>
                            <td class="px-6 py-4">
                                {{ round($valor, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


   

    <div class="flex justify-center items-center min-h-screen">
        <a href="{{ route('profesores.listar') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            Volver a la lista de profesores
        </a>
    </div>
    

     <!-- Script para crear las gráficas -->

     <script>
        document.addEventListener('DOMContentLoaded', function() {
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
});

    </script>
</x-app-layout>