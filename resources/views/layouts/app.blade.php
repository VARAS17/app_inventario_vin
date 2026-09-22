<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">

    <div class="flex min-h-screen">

        {{-- SIDEBAR --}}
        <aside class="flex flex-col w-56 min-h-screen bg-white border-r border-gray-200 fixed top-0 left-0 z-50">

            {{-- Logo --}}
            <div class="flex flex-col items-center gap-3 px-4 py-6 border-b border-gray-200">
                <div class="w-24 h-24 flex-shrink-0 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('logovice.jpg') }}" 
                        alt="Logo Vicerrectorado" 
                        class="w-full h-full object-contain">
                </div>
                
                <div class="text-center">
                    <span class="block font-bold text-gray-900 text-base leading-tight uppercase tracking-tight">
                        Inventario
                    </span>
                    <span class="block text-gray-500 font-medium text-xs uppercase tracking-widest mt-0.5">
                        Vicerrectorado de Investigacion
                    </span>
                </div>
            </div>

            {{-- Navegación --}}
            <nav class="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto">
                <p class="px-2 pt-2 pb-1 text-xs font-medium text-gray-400 uppercase tracking-widest">General</p>

                {{-- DASHBOARD --}}
                <a href="{{ route('dashboard') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                        {{ request()->routeIs('dashboard') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('areavin') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                        {{ request()->routeIs('areavin') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Creacion de Areas
                </a>

                {{-- PERSONAL --}}
                <a href="{{ route('personal') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                        {{ request()->routeIs('personal') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Personal
                </a>
                
                {{-- MENU DESPLEGABLE: EQUIPO TECNOLOGICO --}}
                @php
                    // Definimos las rutas hijas para verificar si alguna está activa
                    $rutasTec = ['inventariotec', 'trasnferencia-tec', 'prestamo-tec', 'salida-tec', 'mantenimiento-tec','trazabilidad-tec'];
                    $isTecActive = request()->routeIs($rutasTec);
                @endphp

                <div class="space-y-0.5">
                    <button type="button" 
                            id="btnTecnologico"
                            class="w-full flex items-center justify-between gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors focus:outline-none {{ $isTecActive ? 'text-blue-600 bg-blue-50/60 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Equipo Tecnológico</span>
                        </div>
                        <!-- Si está activo, la flecha se queda girada -->
                        <svg id="arrowTec" class="w-4 h-4 transition-transform duration-200 {{ $isTecActive ? 'rotate-180 text-blue-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Si está activo, se quita la clase 'hidden' y se pone 'flex' -->
                    <div id="menuTecnologico" class="{{ $isTecActive ? 'flex' : 'hidden' }} flex-col pl-9 space-y-0.5 overflow-hidden transition-all duration-300">
                        
                        <a href="{{ route('inventariotec') }}" 
                        class="block px-3 py-1.5 text-xs rounded-md transition-colors {{ request()->routeIs('inventariotec') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                            Ver Todo
                        </a>

                        <a href="{{ route('trasnferencia-tec') }}" 
                        class="block px-3 py-1.5 text-xs rounded-md transition-colors {{ request()->routeIs('trasnferencia-tec') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                            Transferencia
                        </a>

                        <a href="{{ route('prestamo-tec') }}" 
                        class="block px-3 py-1.5 text-xs rounded-md transition-colors {{ request()->routeIs('prestamo-tec') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                            Prestamo
                        </a>

                        <a href="{{ route('salida-tec') }}" 
                        class="block px-3 py-1.5 text-xs rounded-md transition-colors {{ request()->routeIs('salida-tec') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                            Salida
                        </a>

                        <a href="{{ route('mantenimiento-tec') }}" 
                        class="block px-3 py-1.5 text-xs rounded-md transition-colors {{ request()->routeIs('mantenimiento-tec') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                            Mantenimiento
                        </a>
        
                        <a href="{{ route('trazabilidad-tec') }}" 
                        class="block px-3 py-1.5 text-xs rounded-md transition-colors {{ request()->routeIs('trazabilidad-tec') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                            Trazabilidad
                        </a>
                    </div>
                </div>

                {{-- MOBILIARIO --}}
                <a href="{{ route('inventariomobi') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                        {{ request()->routeIs('inventariomobi') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Mobiliario
                </a>

                {{-- UTILES --}}
                <a href="{{ route('inventarioutil') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                        {{ request()->routeIs('inventarioutil') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Útiles de oficina
                </a>

                {{-- DADO DE BAJA --}}
                <a href="{{ route('debaja') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                        {{ request()->routeIs('debaja') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    DADO DE BAJA
                </a>
                            
            </nav>

            {{-- Footer: usuario --}}
            <div class="border-t border-gray-200 p-3 relative" id="userSection">
                <div id="userDropdown"
                     class="hidden absolute bottom-full left-3 right-3 mb-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50">
                    <a href="{{ route('profile') }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Mi perfil
                    </a>
                    <div class="my-1 border-t border-gray-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Cerrar sesión
                        </button>
                    </form>
                </div>

                <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer" id="userRow">
                    @php
                        $initials = strtoupper(substr(auth()->user()->nombre, 0, 1));
                        $initials .= strtoupper(substr(auth()->user()->apellido, 0, 1));
                    @endphp
                    <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-xs font-semibold">{{ $initials }}</span>
                    </div>
                    <p class="text-sm font-medium text-gray-800 truncate">
                        {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}
                    </p>
                    <div class="ml-auto text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <div class="flex-1 flex flex-col ml-56">
            @if (isset($header))
                <header class="bg-white border-b border-gray-200">
                    <div class="px-6 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        // Lógica para el Dropdown de Usuario
        const userRow = document.getElementById('userRow');
        const userDropdown = document.getElementById('userDropdown');

        userRow.addEventListener('click', function () {
            userDropdown.classList.toggle('hidden');
        });

        // Lógica para el Menú Desplegable de Equipo Tecnológico
        const btnTecnologico = document.getElementById('btnTecnologico');
        const menuTecnologico = document.getElementById('menuTecnologico');
        const arrowTec = document.getElementById('arrowTec');

        btnTecnologico.addEventListener('click', function() {
            const isHidden = menuTecnologico.classList.contains('hidden');
            
            if (isHidden) {
                menuTecnologico.classList.remove('hidden');
                menuTecnologico.classList.add('flex');
                arrowTec.classList.add('rotate-180');
            } else {
                menuTecnologico.classList.add('hidden');
                menuTecnologico.classList.remove('flex');
                arrowTec.classList.remove('rotate-180');
            }
        });

        // Opcional: Mantener abierto si la ruta actual es una de las subrutas
        // Esto es útil cuando recargas la página
        if (window.location.href.includes('inventariotec')) {
            menuTecnologico.classList.remove('hidden');
            menuTecnologico.classList.add('flex');
            arrowTec.classList.add('rotate-180');
            btnTecnologico.classList.add('bg-gray-50', 'text-gray-900');
        }

        // Cerrar dropdown de usuario si se hace click fuera
        document.addEventListener('click', function (e) {
            if (!userRow.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    </script>

</body>
</html>