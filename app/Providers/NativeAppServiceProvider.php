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

        Artisan::call('migrate', ['--force' => true]);

        // 1. Verificar el ADMIN
        if (!\App\Models\User::where('email', 'admin@universidad.edu')->exists()) {
            Artisan::call('db:seed', ['--force' => true]);
        }

        // 2. Verificar el PERSONAL (Añade esto)
        // Esto asegura que si agregaste gente al seeder, se suban aunque el admin ya exista
        if (\App\Models\Personal::count() === 0) {
            Artisan::call('db:seed', [
                '--class' => 'PersonalSeeder', 
                '--force' => true
            ]);
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