<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $nombre = '';
    public string $apellido = '';
    public string $cargo = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->nombre = $user->nombre;
        $this->apellido = $user->apellido;
        $this->cargo = $user->cargo ?? '';
        $this->email = $user->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'cargo' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->nombre . ' ' . $user->apellido);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Información del Perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Actualiza la información de tu cuenta y tu dirección de correo electrónico.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <!-- Nombre -->
        <div>
            <x-input-label for="nombre" :value="__('Nombre')" />
            <x-text-input wire:model="nombre" id="nombre" name="nombre" type="text" class="mt-1 block w-full" required autofocus autocomplete="given-name" />
            <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
        </div>

        <!-- Apellido -->
        <div>
            <x-input-label for="apellido" :value="__('Apellido')" />
            <x-text-input wire:model="apellido" id="apellido" name="apellido" type="text" class="mt-1 block w-full" required autocomplete="family-name" />
            <x-input-error class="mt-2" :messages="$errors->get('apellido')" />
        </div>

        <!-- Cargo -->
        <div>
            <x-input-label for="cargo" :value="__('Cargo')" />
            <x-text-input wire:model="cargo" id="cargo" name="cargo" type="text" class="mt-1 block w-full" placeholder="Ej: Director, Asistente" autocomplete="organization-title" />
            <x-input-error class="mt-2" :messages="$errors->get('cargo')" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Tu dirección de correo no está verificada.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Haz clic aquí para re-enviar el correo de verificación.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('Se ha enviado un nuevo enlace de verificación a tu correo.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Guardado.') }}
            </x-action-message>
        </div>
    </form>
</section>