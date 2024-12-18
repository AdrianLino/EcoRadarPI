<nav x-data="{ darkMode: localStorage.getItem('dark-mode') === 'true' || false }" 
     x-init="$watch('darkMode', value => localStorage.setItem('dark-mode', value))"
     :class="{ 'dark': darkMode }" 
     class="sticky top-0 bg-[#800020] dark:bg-[#800020] border-b border-gray-100 dark:border-gray-700 z-10">
    
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white dark:text-white" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <span class="text-white dark:text-white ">{{ __('Inicio') }}</span>
                    </x-nav-link>
                    <x-nav-link :href="route('profesores.listar')" :active="request()->routeIs('profesores.listar')">
                        <span class="text-white dark:text-white">{{ __('Profesores') }}</span>
                    </x-nav-link>
                    <x-nav-link :href="route('panel.general')" :active="request()->routeIs('panel.general')">
                        <span class="text-white dark:text-white">{{ __('Carreras') }}</span>
                    </x-nav-link>
                    <x-nav-link :href="route('calificaciones.seleccionar_carrera')" :active="request()->routeIs('panel.general')">
                        <span class="text-white dark:text-white">{{ __('Calificaciones') }}</span>
                    </x-nav-link>
                </div>
            </div>

            <!-- Dark Mode Toggle Button -->
            <div class="flex items-center space-x-4">
                <button @click="darkMode = !darkMode" 
                        class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1 rounded">
                    <span x-show="!darkMode">🌞</span>
                    <span x-show="darkMode">🌙 </span>
                </button>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white dark:text-white bg-[#800020] dark:bg-[#800020] hover:text-gray-100 dark:hover:text-gray-100 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4 text-white dark:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @role('super admin')
                            <x-dropdown-link :href="route('encuestas.mostrar')">
                                {{ __('Mostrar Encuestas') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('encuesta.index')">
                                {{ __('Subir Encuestas') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('datos.index')">
                                {{ __('Mostrar Calificaciones') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('datos.import.form')">
                                {{ __('Subir Calificaciones') }}
                            </x-dropdown-link>
                            @endrole

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white dark:text-white hover:text-gray-100 dark:hover:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-200 dark:focus:bg-gray-900 focus:text-gray-100 dark:focus:text-gray-100 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <span class="text-white dark:text-white">{{ __('Inicio') }}</span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('profesores.listar')" :active="request()->routeIs('profesores.listar')">
                <span class="text-white dark:text-white">{{ __('Profesores') }}</span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('panel.general')" :active="request()->routeIs('panel.general')">
                <span class="text-white dark:text-white">{{ __('Carreras') }}</span>
            </x-responsive-nav-link>
            
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
