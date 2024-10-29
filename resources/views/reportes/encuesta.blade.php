<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subir reportes') }}
        </h2>
    </x-slot>

    <h1>Subir Encuesta Docente</h1>

    <!-- Mostrar mensaje de éxito si existe -->
    @if (session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Formulario para subir el archivo Excel -->
    <form action="{{ route('encuesta.importar') }}" method="POST" enctype="multipart/form-data">
        @csrf <!-- Protege el formulario con un token CSRF -->

        <!-- Campo para seleccionar el archivo Excel -->
        <div>
            <label for="archivo">Selecciona el archivo Excel:</label>
            <input type="file" name="archivo" id="archivo" required>
        </div>

        <!-- Botón para importar el archivo -->
        <div>
            <button type="submit">Importar</button>
        </div>
    </form>

</x-app-layout>