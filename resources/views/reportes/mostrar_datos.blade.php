<h1>Datos Importados</h1>

<table border="1">
    @foreach ($datos[0] as $fila)
        <tr>
            @foreach ($fila as $celda)
                <td>{{ $celda }}</td>
            @endforeach
        </tr>
    @endforeach
</table>
