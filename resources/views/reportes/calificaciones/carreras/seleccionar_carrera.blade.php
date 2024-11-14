<div class="container">
    <h1>Seleccione una Carrera para Ver Calificaciones</h1>
    <ul>
        @foreach($carreras as $key => $nombre)
            <li>
                <a href="{{ route('calificaciones.mostrar', $key) }}" class="btn btn-primary">{{ $nombre }}</a>
            </li>
        @endforeach
    </ul>
</div>
