<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lista de Encuestas Docentes') }}
        </h2>
    </x-slot>

<table>
    <thead>
        <tr>
            <th>PERIODO</th>
            <th>MODALIDAD</th>
            <th>GRUPO</th>
            <th>DIRECCIÓN</th>
            <th>PROFESOR</th>
            <th>ASIGNATURA</th>
            <th>EVALUACIÓN</th>
            <th>ENCUESTAS</th>
            <!-- Respuestas de 1 a 20 -->
            @for ($i = 1; $i <= 20; $i++)
                <th>Respuesta {{ $i }}</th>
            @endfor
            <!-- Comentarios de 1 a 13 -->
            @for ($i = 1; $i <= 13; $i++)
                <th>Comentario {{ $i }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach ($encuestas as $encuesta)
            <tr>
                <td>{{ $encuesta->periodo }}</td>
                <td>{{ $encuesta->modalidad }}</td>
                <td>{{ $encuesta->grupo }}</td>
                <td>{{ $encuesta->direccion }}</td>
                <td>{{ $encuesta->profesor }}</td>
                <td>{{ $encuesta->asignatura }}</td>
                <td>{{ $encuesta->evaluacion }}</td>
                <td>{{ $encuesta->encuestas }}</td>

                <!-- Respuestas de 1 a 20 -->
                @for ($i = 1; $i <= 20; $i++)
                    <td>{{ $encuesta["respuesta_$i"] }}</td>
                @endfor

                <!-- Comentarios de 1 a 13 -->
                @for ($i = 1; $i <= 13; $i++)
                    <td>{{ $encuesta["comentario_$i"] }}</td>
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>

</x-app-layout>


<style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>