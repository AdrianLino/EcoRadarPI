


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Calificaciones por Carrera') }}
        </h2>
    </x-slot>


    <div class="container">

    <ul class="list-group">
        @foreach(['ADMON.NEG.MIXTA', 'MERCADOTECNIA MIXTA', 'DERECHO MIXTA','ADMON. NEG. ESCOLARI','MERCADOTECNIA',
         'DERECHO ESCOLARIZADA','INGENIERO ARQUITECTO', 'ADMON. NEG. INT.', 'DERECHO', 'DISEÑO IND.DES.PROD.', 'PROD. ANIM. ESCOLARI'
        ] as $carrera)
            <li class="list-group-item">
                <a href="{{ route('calificaciones.por_carrera', $carrera) }}">
                    {{ $carrera }}
                </a>
            </li>
        @endforeach
    </ul>
</div>


</x-app-layout>
