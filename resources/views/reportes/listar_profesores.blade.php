<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lista de Profesores') }}
        </h2>
    </x-slot>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="{{ route('profesores.listar') }}" class="max-w-sm mx-auto">
        <label for="search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Buscar profesor</label>
        <div class="flex">
          <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"/>
            </svg>
          </span>
          <input type="text" id="search" name="search" value="{{ request()->search }}" class="rounded-none rounded-e-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Buscar profesor...">
          <button type="submit" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Buscar</button>
        </div>
      </form>
      

    <!-- Mostrar lista de profesores -->
    <div class="max-w-4xl mx-auto mt-10">
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($profesores as $profesor)
                <li class="py-4">
                    <a href="{{ route('profesores.detalles', $profesor) }}" 
                       class="block text-lg font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                        {{ $profesor }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    
</x-app-layout>



<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lista de Profesores') }}
        </h2>
    </x-slot>


</x-app-layout>