<!-- resources/views/profesores.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Reportes de encuestas') }}
        </h2>
    </x-slot>

    <!-- Usamos Alpine.js para manejar el estado de las pestañas -->
    <div x-data="{ tab: 'tab1' }" class="py-12">

        <!-- Barra de pestañas -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <!-- Pestaña 1 -->
                    <a href="#"
                       @click.prevent="tab = 'tab1'"
                       :class="tab === 'tab1' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                       class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Profesores
                    </a>
                    <!-- Pestaña 2 -->
                    <a href="#"
                       @click.prevent="tab = 'tab2'"
                       :class="tab === 'tab2' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                       class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Componente 2
                    </a>
                    <!-- Agrega más pestañas si lo deseas -->
                </nav>
            </div>
        </div>

        <!-- Contenido de las pestañas -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <!-- Componente 1 -->
            <div x-show="tab === 'tab1'">
                <!-- Aquí puedes incluir tu componente o contenido del Componente 1 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <x-profesores></x-profesores>
                    </div>
                </div>
            </div>

            <!-- Componente 2 -->
            <div x-show="tab === 'tab2'">
                <!-- Aquí puedes incluir tu componente o contenido del Componente 2 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium">Contenido del Componente 2</h3>
                        <p>Este es el contenido de la segunda pestaña.</p>
                        <!-- Puedes incluir más contenido o componentes aquí -->
                    </div>
                </div>
            </div>

            <!-- Agrega más secciones para más pestañas si lo deseas -->
        </div>
    </div>
</x-app-layout>
