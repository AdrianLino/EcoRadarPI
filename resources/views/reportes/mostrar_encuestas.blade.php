<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuestas Docentes</title>
    <!-- Agrega estilos si es necesario, por ejemplo, usando Tailwind CSS o Bootstrap -->
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
</head>
<body>

<h1>Lista de Encuestas Docentes</h1>

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

</body>
</html>
