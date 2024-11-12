<div class="container">
    <h1>Datos de Calificaciones</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('datos.delete') }}" method="POST">
        @csrf
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
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
                    <td><input type="checkbox" name="ids[]" value="{{ $dato->id }}"></td>
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

        <button type="submit" class="btn btn-danger">Eliminar Seleccionados</button>
    </form>

    <a href="{{ route('datos.import.form') }}" class="btn btn-success mt-3">Importar Datos</a>
</div>

<script>
    // Seleccionar o deseleccionar todos los checkboxes
    document.getElementById('select-all').onclick = function() {
        var checkboxes = document.getElementsByName('ids[]');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    }
</script>
