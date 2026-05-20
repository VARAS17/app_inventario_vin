<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Debaja as DebajaModel; 
use Illuminate\Support\Facades\Storage; // IMPORTANTE: Para manejar el borrado de archivos

class Debaja extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    public $search = '';

    public function render()
    {
        // Buscamos en la tabla de bajas
        $bajas = DebajaModel::with('personal')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('tipo_inventario', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.inventario.debaja', [
            'bajas' => $bajas
        ]);
    }

    public function eliminarPermanente($id)
    {
        $registro = DebajaModel::findOrFail($id);

        // 1. Borrar el archivo físico del disco local si existe
        if ($registro->imagen) {
            // Esto busca en storage/app/ y borra la ruta guardada
            Storage::disk('local')->delete($registro->imagen);
        }

        // 2. Borrar el registro de la base de datos
        $registro->delete();

        // 3. Despachar mensaje de éxito (ajusta el nombre del evento si usas otro en tu JS)
        $this->dispatch('mueble-guardado', msg: 'Registro y archivo eliminados correctamente');
    }
}