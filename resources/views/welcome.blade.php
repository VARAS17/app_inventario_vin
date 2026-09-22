<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Inventario - Vicerrectorado de Investigación</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans">
        <div class="flex flex-col lg:flex-row min-h-screen w-full">
            
            <!-- LADO IZQUIERDO: IMAGEN DE FONDO -->
            <div class="hidden lg:block lg:w-1/2 bg-cover bg-center" 
                 style="background-image: url('{{ asset('inicio.jpeg') }}');">
                <!-- Este div se oculta en móviles y se muestra en pantallas grandes -->
            </div>

            <!-- LADO DERECHO: CONTENIDO -->
            <div class="w-full lg:w-1/2 flex flex-col items-center justify-center bg-white p-8 lg:p-16">
                
                <!-- Logo -->
                <div class="mb-8">
                    <img src="{{ asset('logovice.jpg') }}" alt="Logo Vicerrectorado" class="h-32 lg:h-48 w-auto object-contain">
                </div>

                <!-- Título Bienvenida -->
                <div class="text-center mb-10">
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 leading-tight uppercase">
                        Bienvenido al Sistema de Inventario del Vicerrectorado de Investigación
                    </h1>
                </div>

                <!-- Botón Log In -->
                <div class="w-full max-w-xs">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" 
                               class="flex justify-center items-center w-full px-6 py-4 bg-blue-600 text-white font-bold rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out uppercase tracking-widest text-sm">
                                Ir al Tablero
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="flex justify-center items-center w-full px-6 py-4 bg-[#FF2D20] text-white font-bold rounded-lg shadow-md hover:bg-[#e0271a] transition duration-300 ease-in-out uppercase tracking-widest text-sm">
                                Log In
                            </a>
                        @endauth
                    @endif
                </div>

                <!-- Footer -->
                <footer class="mt-16 text-center text-xs text-gray-500 font-medium">
                    Desarrollado por el equipo de TI del Vicerrectorado de Investigación ({{ date('Y') }})
                </footer>
            </div>

        </div>
    </body>
</html>