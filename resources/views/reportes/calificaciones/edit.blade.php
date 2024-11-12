
<div class="container">
    <h1>Editar Registro</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('datos.update', $dato->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" class="form-control" value="{{ $dato->descripcion }}">
        </div>
        <div class="form-group">
            <label>Descripción Breve</label>
            <input type="text" name="descripcion_breve" class="form-control" value="{{ $dato->descripcion_breve }}">
        </div>
        <div class="form-group">
            <label>Apellidos Nombre</label>
            <input type="text" name="apellidos_nombre" class="form-control" value="{{ $dato->apellidos_nombre }}">
        </div>
        <div class="form-group">
            <label>Ciclo</label>
            <input type="text" name="ciclo" class="form-control" value="{{ $dato->ciclo }}">
        </div>
        <div class="form-group">
            <label>Matrícula</label>
            <input type="text" name="matricula" class="form-control" value="{{ $dato->matricula }}">
        </div>
        <div class="form-group">
            <label>Evaluación</label>
            <input type="text" name="evaluacion" class="form-control" value="{{ $dato->evaluacion }}">
        </div>
        <div class="form-group">
            <label>Valor</label>
            <input type="number" step="0.01" name="valor" class="form-control" value="{{ $dato->valor }}">
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('datos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
