<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Carrera: ') }}{{ $carrera }}
            </h2>
    
            <a href="{{ route('panel.general') }}" class="ml-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                Volver a la lista de carreras
            </a>
        </div>
    </x-slot>


    <div class="max-w-md mx-auto my-8">
        <!-- Filtros de selección de periodo y modalidad -->
        <form method="GET" action="{{ route('carrera.detalles', $carrera) }}">
            <!-- Filtro de Periodo -->
            <label for="periodo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                Selecciona un periodo:
            </label>
            <select name="periodo" id="periodo" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 mb-4" onchange="this.form.submit()">
                <option value="todos" {{ $periodoSeleccionado === 'todos' ? 'selected' : '' }}>Todos los periodos</option>
                @foreach ($periodos as $periodo)
                    <option value="{{ $periodo->periodo }}" {{ $periodoSeleccionado === $periodo->periodo ? 'selected' : '' }}>
                        {{ $periodo->periodo }}
                    </option>
                @endforeach
            </select>
    
            <!-- Filtro de Modalidad -->
            <label for="modalidad" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                Selecciona una modalidad:
            </label>
            <select name="modalidad" id="modalidad" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange="this.form.submit()">
                <option value="todas" {{ $modalidadSeleccionada === 'todas' ? 'selected' : '' }}>Todas</option>
                @foreach ($modalidades as $modalidad)
                    <option value="{{ $modalidad }}" {{ $modalidadSeleccionada === $modalidad ? 'selected' : '' }}>
                        {{ $modalidad }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    
    
    <!-- Mostrar el promedio general de la carrera -->
    <div class="text-center my-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
            Promedio General de Evaluación
        </h2>
        <p class="text-4xl font-semibold text-blue-600 dark:text-blue-400 mt-2">
            {{ round($promedioEvaluacion, 2) }}
        </p>
    </div>




        <!-- Gráfica de barras para las respuestas del 1 al 19 relacionadas con las preguntas -->


<div class="w-full max-w-4xl mx-auto mt-10">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <canvas id="promedioPorPeriodoChart"></canvas> <!-- Aquí se renderizará la gráfica de períodos -->
    </div>
</div>

        
    
<!-- Ranking de 10 mejores y peores profesores -->
<div class="w-full mt-10 flex justify-center">
    <!-- Tabla de los 10 mejores profesores -->
    <div class="w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4 text-center">Top 10 Mejores Profesores</h3>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Profesor</th>
                        <th scope="col" class="px-6 py-3">Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mejoresProfesores as $profesor => $promedio)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $profesor }}
                            </td>
                            <td class="px-6 py-4">
                                {{ round($promedio, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabla de los 10 peores profesores -->
    <div class="w-full max-w-lg ml-6">
        <h3 class="text-lg font-semibold mb-4 text-center">Top 10 Peores Profesores</h3>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Profesor</th>
                        <th scope="col" class="px-6 py-3">Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($peoresProfesores as $profesor => $promedio)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $profesor }}
                            </td>
                            <td class="px-6 py-4">
                                {{ round($promedio, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Top 5 Preguntas con mejores y peores promedios -->
<div class="w-full mt-12 flex justify-center">
    <!-- Tabla de las 5 mejores preguntas -->
    <div class="w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4 text-center">Top 5 Mejores Preguntas</h3>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Pregunta</th>
                        <th scope="col" class="px-6 py-3">Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mejoresRespuestas as $key => $valor)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $preguntas[(int)str_replace('respuesta_', '', $key) - 1] }}
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

    <!-- Tabla de las 5 peores preguntas -->
    <div class="w-full max-w-lg ml-6">
        <h3 class="text-lg font-semibold mb-4 text-center">Top 5 Peores Preguntas</h3>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Pregunta</th>
                        <th scope="col" class="px-6 py-3">Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($peoresRespuestas as $key => $valor)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $preguntas[(int)str_replace('respuesta_', '', $key) - 1] }}
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


    
    <!-- Evaluaciones por materias -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-8">
        <h3 class="text-lg font-semibold mb-4 text-center">Evaluaciones por Materia</h3>
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Materia</th>
                    <th scope="col" class="px-6 py-3">Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($evaluacionesMaterias as $materia => $promedio)
                    <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $materia }}
                        </td>
                        <td class="px-6 py-4">
                            {{ round($promedio, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    

    <!-- Script para la gráfica de línea -->
    <script>
        var ctx = document.getElementById('promedioPorPeriodoChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($promediosPorPeriodo->keys()) !!},  // Los periodos ordenados
                datasets: [{
                    label: 'Promedio de Evaluación por Período',
                    data: {!! json_encode($promediosPorPeriodo->values()) !!},  // Los promedios
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 0.5
                        }
                    }
                }
            }
        });
    </script>

<div class="flex justify-center items-center min-h-screen">
    <a href="{{ route('panel.general') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
        Volver a la lista de carreras
    </a>
</div>

    

</x-app-layout>