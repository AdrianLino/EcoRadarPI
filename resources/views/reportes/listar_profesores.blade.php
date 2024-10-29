<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lista de Profesores') }}
        </h2>
    </x-slot>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="{{ route('profesores.listar') }}">
        <input type="text" name="search" placeholder="Buscar profesor..." value="{{ request()->search }}">
        <button type="submit">Buscar</button>
    </form>

    <!-- Mostrar lista de profesores -->
    <ul>
        @foreach ($profesores as $profesor)
            <li>
                <a href="{{ route('profesores.detalles', $profesor) }}">{{ $profesor }}</a>
            </li>
        @endforeach
    </ul>
</x-app-layout>