<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Inventario\Inventariotec;
use App\Livewire\Inventario\Inventariomobi;
use App\Livewire\Inventario\Inventarioutil;
use App\Livewire\Inventario\Personal;
use App\Livewire\Inventario\Debaja;
use App\Livewire\Inventario\TrasnferenciaTEC;
use App\Livewire\Inventario\SalidaTEC;
use App\Livewire\Inventario\MantenimientoTEC;
use App\Livewire\Inventario\PrestamoTEC;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('debaja', Debaja::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('debaja');

//MENU DE OPCIONES PARA EL INVENTARIO TECNOLOGICO 

Route::get('inventariotec', Inventariotec::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('inventariotec');

Route::get('trasnferencia-tec', TrasnferenciaTEC::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('trasnferencia-tec');

Route::get('salida-tec', SalidaTEC::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('salida-tec');

Route::get('prestamo-tec', PrestamoTEC::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('prestamo-tec');

Route::get('trasnferencia-tec', TrasnferenciaTEC::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('trasnferencia-tec');

Route::get('mantenimiento-tec', MantenimientoTEC::class)
    ->middleware(['auth', 'verified']) // Añade seguridad
    ->name('mantenimiento-tec');


Route::get('/tecnologia-pdf/{path}', function ($path) {
    if (!Storage::disk('local')->exists($path)) {
        abort(404);
    }
    $file = Storage::disk('local')->get($path);
    $type = Storage::disk('local')->mimeType($path) ?: 'application/pdf';
    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);
    $response->header("Content-Disposition", 'inline; filename="' . basename($path) . '"');
    return $response;
})->name('tecnologia.pdf')->where('path', '.*');
/*
    FIN DEL MENU DE INVENTARIO TECNOLOGICO
*/

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


Route::get('/tecnologia-foto/{path}', function ($path) {
    if (!Storage::disk('local')->exists($path)) {
        abort(404);
    }

    $file = Storage::disk('local')->get($path);
    $type = Storage::disk('local')->mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
})->name('tecnologia.foto')->where('path', '.*');

require __DIR__.'/auth.php';
