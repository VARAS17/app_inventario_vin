<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Util;

class Inventarioutil extends Component
{
    // Aplicamos el layout de Breeze/Jetstream
    #[Layout('layouts.app')]

    // Propiedades del formulario
    public $nombre, $cantidad, $unidad = 'Unidad', $util_id;
    public $search = '';
    public $isOpen = false; // Control del modal

    // Reglas de validación
    protected $rules = [
        'nombre' => 'required|min:3',
        'cantidad' => 'required|numeric|min:0',
        'unidad' => 'required',
    ];

    public function render()
    {
        $utiles = Util::where('nombre', 'like', '%' . $this->search . '%')
            ->latest()
            ->get();

        // IMPORTANTE: La vista ahora está en livewire.inventario.inventarioutil
        return view('livewire.inventario.inventarioutil', [
            'utiles' => $utiles
        ]);
    }

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function editar($id)
    {
        $articulo = Util::findOrFail($id);
        $this->util_id = $id;
        $this->nombre = $articulo->nombre;
        $this->cantidad = $articulo->cantidad;
        $this->unidad = $articulo->unidad;

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        // Si util_id existe, actualiza; si no, crea.
        Util::updateOrCreate(['id' => $this->util_id], [
            'nombre' => $this->nombre,
            'cantidad' => $this->cantidad,
            'unidad' => $this->unidad,
        ]);

        session()->flash('message', 
            $this->util_id ? 'Artículo actualizado con éxito.' : 'Artículo agregado al inventario.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function eliminar($id)
    {
        Util::find($id)->delete();
        session()->flash('message', 'Artículo eliminado correctamente.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->nombre = '';
        $this->cantidad = '';
        $this->unidad = 'Unidad';
        $this->util_id = '';
    }
}