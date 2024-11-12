
<div class="container">
    <h1>Importar Datos de Calificaciones</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('datos.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>Archivo Excel o CSV</label>
            <input type="file" name="file" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Acción a realizar</label>
            <div>
                <label>
                    <input type="radio" name="action" value="replace" required> Sustituir Información
                </label>
                <label style="margin-left: 20px;">
                    <input type="radio" name="action" value="append" required> Agregar Información
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Importar</button>
        <a href="{{ route('datos.index') }}" class="btn btn-secondary">Volver</a>
    </form>
</div>
