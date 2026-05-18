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
            <div class="flex items-center gap-3 px-4 py-5 border-b border-gray-200">
                <div class="w-9 h-9 rounded-lg bg-gray-900 flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm">{{ substr(config('app.name', 'L'), 0, 1) }}</span>
                </div>
                <span class="font-semibold text-gray-800 text-sm">{{ config('app.name', 'Laravel') }}</span>
            </div>

            {{-- Navegación --}}
            <nav class="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto">
                <p class="px-2 pt-2 pb-1 text-xs font-medium text-gray-400 uppercase tracking-widest">General</p>

                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('dashboard') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('inventariotec') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('inventariotec') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    INVENTARIOTEC
                </a>
            
                <a href="{{ route('personal') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('personal') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    PERSONAL
                </a>
                <a href="{{ route('inventariomobi') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('inventariomobi') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    INVENTARIOMOBI
                </a>
                <a href="{{ route('inventarioutil') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('inventarioutil') ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    INVENTARIOUTIL
                </a>
                
            </nav>

            {{-- Footer: usuario + menú (sin Alpine, usa JS puro) --}}
            <div class="border-t border-gray-200 p-3 relative" id="userSection">

                {{-- Dropdown --}}
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

                {{-- Fila de usuario --}}
                <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer"
                     id="userRow">

                    {{-- Avatar con iniciales --}}  
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

                    {{-- Ícono de rueda --}}
                    <div class="w-7 h-7 rounded-md flex items-center justify-center text-gray-400
                                hover:text-gray-700 hover:bg-gray-100 border border-transparent
                                hover:border-gray-200 transition-colors flex-shrink-0"
                         id="gearIcon">
                        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
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
        const userRow = document.getElementById('userRow');
        const dropdown = document.getElementById('userDropdown');

        userRow.addEventListener('click', function () {
            dropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', function (e) {
            if (!userRow.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>

</body>
</html>