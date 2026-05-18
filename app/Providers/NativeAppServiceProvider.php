<?php

namespace App\Providers;

use Native\Desktop\Facades\Window;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Illuminate\Support\Facades\Artisan;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open();

        // Corre las migraciones siempre
        Artisan::call('migrate', ['--force' => true]);

        // Solo siembra datos si el usuario admin no existe aún
        if (!\App\Models\User::where('email', 'admin@universidad.edu')->exists()) {
            Artisan::call('db:seed', ['--force' => true]);
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [];
    }
}