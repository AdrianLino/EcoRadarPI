
<div class="container">
    <h1>Datos de Calificaciones</h1>

    <!-- Formulario de búsqueda -->
    <form action="{{ route('datos.index') }}" method="GET" class="mb-3">
        <input type="text" name="search" class="form-control" placeholder="Buscar por Apellidos Nombre" value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary mt-2">Buscar</button>
        <a href="{{ route('datos.index') }}" class="btn btn-secondary mt-2">Limpiar</a>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Descripción Breve</th>
                <th>Apellidos Nombre</th>
                <th>Ciclo</th>
                <th>Matrícula</th>
                <th>Evaluación</th>
                <th>Valor</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $dato)
            <tr>
                <td>{{ $dato->descripcion }}</td>
                <td>{{ $dato->descripcion_breve }}</td>
                <td>{{ $dato->apellidos_nombre }}</td>
                <td>{{ $dato->ciclo }}</td>
                <td>{{ $dato->matricula }}</td>
                <td>{{ $dato->evaluacion }}</td>
                <td>{{ $dato->valor }}</td>
                <td>
                    <a href="{{ route('datos.edit', $dato->id) }}" class="btn btn-primary btn-sm">Editar</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginación -->
    <div class="d-flex justify-content-center">
        {{ $datos->appends(['search' => $search])->links() }}
    </div>
</div>

