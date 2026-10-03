<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="fixed inset-0 z-50 flex flex-col lg:flex-row bg-white overflow-y-auto">
    <!-- 1 y 2. Sección Izquierda: Imagen a pantalla completa -->
    <div class="hidden lg:block lg:w-2/3 relative bg-gray-900">
        <img 
            src="{{ asset('login.png') }}" 
            alt="Fondo de inicio de sesión" 
            class="absolute inset-0 w-full h-full object-cover"
        />
    </div>

    <!-- 3. Sección Derecha: Ingreso de credenciales -->
    <div class="w-full lg:w-1/3 flex items-center justify-center p-6 sm:p-12 md:p-16 bg-white min-h-screen">
        <div class="w-full max-w-md space-y-6">
            
            <!-- Logo y Encabezado -->
            <div class="flex flex-col items-center text-center">
                <img 
                    src="{{ asset('logovice.png') }}" 
                    alt="Logo Vicerrectorado" 
                    class="h-20 w-auto object-contain mb-4"
                />
                <h1 class="text-base sm:text-lg font-bold text-gray-800 tracking-wide leading-snug">
                    BIENVENIDO AL SISTEMA DE INVENTARIO DEL VICERRECTORADO DE INVESTIGACION
                </h1>
            </div>

            <!-- Estatus de sesión -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-4">
                <!-- Correo Electrónico -->
                <div>
                    <x-input-label for="email" value="Ingrese su correo electrónico" />
                    <x-text-input 
                        wire:model="form.email" 
                        id="email" 
                        class="block mt-1 w-full" 
                        type="email" 
                        name="email" 
                        required 
                        autofocus 
                        autocomplete="username" 
                        placeholder="correo@ejemplo.com"
                    />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                <!-- Contraseña con botón de mostrar/ocultar -->
                <div x-data="{ showPassword: false }">
                    <x-input-label for="password" value="Ingrese su contraseña" />
                    
                    <div class="relative mt-1">
                        <x-text-input 
                            wire:model="form.password" 
                            id="password" 
                            class="block w-full pr-10"
                            ::type="showPassword ? 'text' : 'password'"
                            name="password"
                            required 
                            autocomplete="current-password" 
                            placeholder="••••••••"
                        />
                        
                        <!-- Botón del ojo -->
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                            tabindex="-1"
                        >
                            <!-- Ojo tachado (Ocultar) -->
                            <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                            <!-- Ojo normal (Mostrar) -->
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268-2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>

                <!-- Botón de Inicio de Sesión con Spinner de Carga -->
                <div class="pt-2">
                    <x-primary-button 
                        class="w-full justify-center py-3 text-base flex items-center transition ease-in-out duration-150 disabled:opacity-75"
                        wire:loading.attr="disabled"
                        wire:target="login"
                    >
                        <!-- Círculo de Carga animado (Solo visible al procesar) -->
                        <svg 
                            wire:loading 
                            wire:target="login" 
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" 
                            xmlns="http://www.w3.org/2000/svg" 
                            fill="none" 
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>

                        <!-- Texto en estado normal -->
                        <span wire:loading.remove wire:target="login">
                            Iniciar sesión
                        </span>

                        <!-- Texto en estado cargando -->
                        <span wire:loading wire:target="login">
                            Iniciando sesión...
                        </span>
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</div>