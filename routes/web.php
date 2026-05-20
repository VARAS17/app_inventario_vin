<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Inventario\Inventariotec;
use App\Livewire\Inventario\Inventariomobi;
use App\Livewire\Inventario\Inventarioutil;
use App\Livewire\Inventario\Personal;
use App\Livewire\Inventario\Debaja;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('debaja', Debaja::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('debaja');

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


Route::get('/personal-foto/{path}', function ($path) {
    // Verificamos si el archivo existe en el disco local
    if (!Storage::disk('local')->exists($path)) {
        abort(404);
    }

    $file = Storage::disk('local')->get($path);
    $type = Storage::disk('local')->mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
})->name('personal.foto')->where('path', '.*');

// Ejemplo de ruta en web.php
Route::get('/mobiliario-foto/{path}', function ($path) {
    if (!Storage::disk('local')->exists($path)) abort(404);
    $file = Storage::disk('local')->get($path);
    $type = Storage::disk('local')->mimeType($path);
    return response($file)->header('Content-Type', $type);
})->name('mobiliario.foto')->where('path', '.*');

require __DIR__.'/auth.php';
