<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Carreras Disponibles') }}
        </h2>
    </x-slot>



<div class="text-center my-8">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
        Promedio General de Evaluaciones:
    </h2>
    <p class="text-4xl font-semibold text-blue-600 dark:text-blue-400 mt-2">
        {{ round($promedioGeneral, 2) }}
    </p>
</div>




<div class="flex justify-center">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
        @php
            // Array de imágenes
            $imagenes = [
        "https://imgs.search.brave.com/2Y0WFj4xvPqSITMC4qX5fKAcV3Don4Qv6wAsIZzf2Lw/rs:fit:500:0:0:0/g:ce/aHR0cHM6Ly9jZG4u/cGl4YWJheS5jb20v/cGhvdG8vMjAxMy8w/Ny8xMy8xMC81MS9m/b290YmFsbC0xNTc5/MzBfXzQ4MC5wbmc",
        "https://imgs.search.brave.com/XoIb3Z_A1f5AclMvAvNxWxRjFD7Y8MdzY7VUIl4XLPc/rs:fit:500:0:0:0/g:ce/aHR0cHM6Ly93d3cu/dXRlYy5lZHUuc3Yv/YXNzZXRzL2ltZy9p/Y29fZmFjdWx0YWQu/cG5n",
        "https://cdn-icons-png.flaticon.com/512/11933/11933850.png",
        "https://cdn-icons-png.flaticon.com/512/3898/3898082.png",
        "https://cdn-icons-png.flaticon.com/512/5757/5757029.png",
        "https://cdn-icons-png.flaticon.com/512/6889/6889485.png",
        "https://cdn-icons-png.flaticon.com/512/5225/5225530.png",
        "https://cdn-icons-png.flaticon.com/512/5495/5495606.png",
        "https://img.freepik.com/vector-premium/icono-vectorial-animacion-puede-utilizar-conjunto-iconos-produccion-video_717774-69694.jpg"
    ];
        @endphp

        @foreach ($promediosPorCarrera as $index => $carrera)
            <div class="bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 w-full max-w-md mx-auto h-full">
                <a href="{{ route('carrera.detalles', ['carrera' => $carrera->direccion]) }}">
                    <!-- Imágenes estandarizadas a 400px x 250px -->
                    <img class="w-full h-64 object-cover rounded-t-lg" src="{{ $imagenes[$index % count($imagenes)] }}" alt="Imagen de la carrera" />
                </a>
                <div class="p-6 text-center flex flex-col justify-between h-50">
                    <div>
                        <a href="{{ route('carrera.detalles', ['carrera' => $carrera->direccion]) }}">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {{ $carrera->direccion }}
                            </h5>
                        </a>
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">
                            <strong>Promedio:</strong> {{ round($carrera->promedio, 2) }}
                        </p>
                    </div>
                    <a href="{{ route('carrera.detalles', ['carrera' => $carrera->direccion]) }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Ver detalles
                        <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>





   

</x-app-layout>