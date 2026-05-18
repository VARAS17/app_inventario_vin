<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Inventario\Inventariotec;
use App\Livewire\Inventario\Inventariomobi;
use App\Livewire\Inventario\Inventarioutil;
use App\Livewire\Inventario\Personal;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('inventariotec', Inventariotec::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('inventariotec');

Route::get('inventariomobi', Inventariomobi::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('inventariomobi');

Route::get('inventarioutil', Inventarioutil::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('inventarioutil');

Route::get('personal', Personal::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('personal');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
