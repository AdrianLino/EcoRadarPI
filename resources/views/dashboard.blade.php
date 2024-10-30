<!-- resources/views/profesores.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('') }}
        </h2>
    </x-slot>


<div class="max-w-6xl mx-auto mt-7 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <!-- Imagen en lugar de carrusel -->
    <div class="relative h-[600px] overflow-hidden rounded-t-lg">
        <img src="https://static.wixstatic.com/media/b9899b_fafb2d689c864067ab5fc3dd8835cf48~mv2.jpg/v1/fill/w_640,h_516,al_c,q_80,usm_0.66_1.00_0.01,enc_auto/b9899b_fafb2d689c864067ab5fc3dd8835cf48~mv2.jpg" class="block w-full h-full object-cover" alt="Imagen destacada">
    </div>

    <!-- Contenido de la carta -->
    <div class="p-6 text-center">
        <a href="#">
            <h5 class="mb-3 text-4xl font-bold tracking-tight text-gray-900 dark:text-white">Administrador de estadisticas NEU</h5>
        </a>
        <p class="mb-4 font-normal text-xl text-gray-700 dark:text-gray-400">El Administrador de estadísticas NEU es una solución integral diseñada para la gestión y análisis de datos estadísticos de manera eficiente. Este sistema proporciona a los usuarios una plataforma intuitiva que permite recopilar, organizar y visualizar datos, facilitando la toma de decisiones informadas en diversos campos. Con funciones avanzadas de procesamiento y análisis, el Administrador de estadísticas NEU se convierte en una herramienta esencial para cualquier organización que busque optimizar el manejo de su información estadística, impulsando la precisión y velocidad en la interpretación de datos.</p>
        <a href="{{ route('profesores.listar') }}" class="inline-flex items-center px-6 py-3 text-lg font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            Comenzar
            <svg class="rtl:rotate-180 w-5 h-5 ms-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
            </svg>
        </a>
    </div>
</div>






</x-app-layout>







